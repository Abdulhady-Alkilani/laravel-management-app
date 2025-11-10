<?php

namespace App\Filament\Manager\Resources\Manager;

use App\Filament\Manager\Resources\Manager\TaskResource\Pages;
use App\Filament\Manager\Resources\Manager\TaskResource\RelationManagers;
use App\Models\Task;
use App\Models\User;
use App\Models\Workshop; // لاستيراد Workshop model
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Auth;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'المهام';
    protected static ?string $pluralModelLabel = 'المهام';
    protected static ?string $modelLabel = 'مهمة';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('project', function (Builder $query) {
            $query->where('manager_user_id', Auth::id());
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name', fn (Builder $query) => $query->where('manager_user_id', Auth::id()))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('workshop_id', null))
                    ->label('المشروع'),
                Forms\Components\Select::make('workshop_id')
                    // <== هنا التعديل: إضافة getOptionLabelFromRecordUsing
                    ->relationship('workshop', 'name', fn (Builder $query, Forms\Get $get) => $query->where('project_id', $get('project_id')))
                    ->getOptionLabelFromRecordUsing(fn (Workshop $record) => $record->name) // <== عرض اسم الورشة
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('assigned_to_user_id', null))
                    ->label('الورشة'),
                RichEditor::make('description')
                    ->required()
                    ->columnSpanFull()
                    ->label('وصف المهمة'),
                Forms\Components\TextInput::make('progress')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required()
                    ->live()
                    ->label('التقدم (%)'),
                Forms\Components\Select::make('status')
                    ->options([
                        'لم تبدأ' => 'لم تبدأ', 'قيد التنفيذ' => 'قيد التنفيذ',
                        'مكتملة' => 'مكتملة', 'متوقفة' => 'متوقفة',
                    ])
                    ->required()
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
                    ->options(function (Forms\Get $get) {
                        $workshopId = $get('workshop_id');
                        if (!$workshopId) { return []; }
                        $workersInWorkshop = Workshop::find($workshopId)?->workers;
                        if (!$workersInWorkshop) { return []; }
                        return $workersInWorkshop->filter(function ($user) {
                            return $user->hasRole('Worker') || $user->hasRole('Architectural Engineer') ||
                                   $user->hasRole('Civil Engineer') ||
                                   $user->hasRole('Structural Engineer') ||
                                   $user->hasRole('Electrical Engineer') ||
                                   $user->hasRole('Mechanical Engineer') ||
                                   $user->hasRole('Geotechnical Engineer') ||
                                   $user->hasRole('Quantity Surveyor') ||
                                   $user->hasRole('Site Engineer') ||
                                   $user->hasRole('Environmental Engineer') ||
                                   $user->hasRole('Surveying Engineer'); /* ... Engineer Roles ... */ ;
                        })->pluck('name', 'id')->toArray();
                    })
                    ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('العامل المسؤول')
                    ->helperText('اختر عاملاً أو مهندساً مرتبطاً بالورشة المختارة.'),
                Forms\Components\TextInput::make('estimated_cost')
                    ->numeric()
                    ->nullable()
                    ->prefix('SR')
                    ->label('التكلفة التقديرية'),
                Forms\Components\TextInput::make('actual_cost')
                    ->numeric()
                    ->nullable()
                    ->prefix('SR')
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
                    ->label('الوصف'),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('المشروع')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('workshop.name')
                    ->label('الورشة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('العامل المسؤول')
                    ->searchable()
                    ->sortable(),
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
                Tables\Actions\ViewAction::make(),
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
            'view' => Pages\ViewTask::route('/{record}'),
        ];
    }
}