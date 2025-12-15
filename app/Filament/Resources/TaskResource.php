<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;
use App\Models\User;
use App\Models\Workshop;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Closure;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'المهام';
    protected static ?string $pluralModelLabel = 'المهام';
    protected static ?string $modelLabel = 'مهمة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('workshop_id', null))
                    ->label('المشروع')
                    ->disabledOn('edit'), // <== التعديل الرئيسي هنا: تعطيل عند التعديل

                Forms\Components\Select::make('workshop_id')
                    ->relationship('workshop', 'name', fn (Builder $query, Forms\Get $get) =>
                        $query->when($get('project_id'), fn (Builder $query, $projectId) => $query->where('project_id', $projectId))
                    )
                    ->getOptionLabelFromRecordUsing(fn (Workshop $record) => $record->name)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('assigned_to_user_id', null))
                    ->label('الورشة')
                    ->disabledOn('edit'), // <== التعديل الرئيسي هنا: تعطيل عند التعديل
                
                RichEditor::make('description')
                    ->required()
                    ->columnSpanFull()
                    ->label('وصف المهمة'),
                
                Forms\Components\TextInput::make('progress')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required()
                    ->default(0)
                    ->live()
                    ->label('التقدم (%)'),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'لم تبدأ' => 'لم تبدأ',
                        'قيد التنفيذ' => 'قيد التنفيذ',
                        'مكتملة' => 'مكتملة',
                        'متوقفة' => 'متوقفة',
                    ])
                    ->required()
                    ->default('لم تبدأ')
                    ->label('الحالة')
                    ->rules([
                        fn (Forms\Get $get): Closure =>
                            function (string $attribute, $value, Closure $fail) use ($get) {
                                if ($value === 'مكتملة' && (int)$get('progress') < 100) {
                                    $fail('لا يمكن وضع حالة "مكتملة" إلا إذا كانت نسبة التقدم 100%.');
                                }
                                if ($value !== 'مكتملة' && (int)$get('progress') === 100) {
                                    $fail('إذا كانت نسبة التقدم 100%، فيجب أن تكون حالة المهمة "مكتملة".');
                                }
                            },
                    ]),
                
                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->label('تاريخ البدء المخطط'),
                
                Forms\Components\DatePicker::make('end_date_planned')
                    ->required()
                    ->label('تاريخ الانتهاء المخطط'),
                
                Forms\Components\DatePicker::make('actual_end_date')
                    ->nullable()
                    ->label('تاريخ الانتهاء الفعلي')
                    ->visible(fn (Forms\Get $get) => $get('status') === 'مكتملة'),
                
                Forms\Components\Select::make('assigned_to_user_id')
                    ->label('العامل المسؤول')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('اختر عاملاً أو مهندساً مرتبطاً بالورشة المختارة.')
                    ->options(function (Forms\Get $get): array {
                        $workshopId = $get('workshop_id');
                        if (!$workshopId) { return []; }

                        $workersInWorkshop = Workshop::find($workshopId)?->workers;
                        if (!$workersInWorkshop) { return []; }

                        $engineerAndWorkerRoles = [
                            'Worker', 'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
                            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
                            'Environmental Engineer', 'Surveying Engineer', 'Information Technology Engineer', 'Telecommunications Engineer',
                        ];

                        $eligibleEmployees = collect();
                        $workshop = Workshop::find($workshopId);
                        if ($workshop && $workshop->supervisor) {
                            $eligibleEmployees->push($workshop->supervisor);
                        }

                        $eligibleEmployees = $eligibleEmployees->merge($workersInWorkshop->filter(function ($user) use ($engineerAndWorkerRoles) {
                            return $user->hasRole('Worker') || collect($engineerAndWorkerRoles)->contains(fn ($role) => $user->hasRole($role));
                        }));

                        return $eligibleEmployees->unique('id')
                                ->mapWithKeys(fn (User $user) => [$user->id => "{$user->first_name} {$user->last_name} ({$user->email})"])
                                ->toArray();
                    })
                    ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->disabledOn('edit'), // <== التعديل الرئيسي هنا: تعطيل عند التعديل
                
                Forms\Components\TextInput::make('estimated_cost')
                    ->numeric()
                    ->nullable()
                    ->prefix('SYP')
                    ->label('التكلفة التقديرية'),
                
                Forms\Components\TextInput::make('actual_cost')
                    ->numeric()
                    ->nullable()
                    ->prefix('SYP')
                    ->label('التكلفة الفعلية'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->html()
                    ->label('الوصف'),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('المشروع')
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('project', fn (Builder $subQuery) => 
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('workshop.name')
                    ->label('الورشة')
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('workshop', fn (Builder $subQuery) => 
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('العامل المسؤول')
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('assignedTo', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
            ),
                    // ->sortable(),
                Tables\Columns\TextColumn::make('progress')
                    ->label('التقدم (%)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'لم تبدأ' => 'info', 'قيد التنفيذ' => 'primary',
                        'مكتملة' => 'success', 'متوقفة' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date_planned')
                    ->label('تاريخ الانتهاء المخطط')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}