<?php

namespace App\Filament\Manager\Resources\Manager\ProjectResource\RelationManagers;

use App\Models\Task;
use App\Models\User;
use App\Models\Workshop;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Auth;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';
    protected static ?string $title = 'المهام';
    protected static ?string $pluralTitle = 'المهام';
    protected static ?string $modelLabel = 'مهمة';
    protected static ?string $pluralModelLabel = 'مهام';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // === تعديل حقل المشروع (project_id) ===
                Forms\Components\Hidden::make('project_id')
                    ->default($this->getOwnerRecord()->id)
                    ->dehydrated(true)
                    ->visible(fn (string $operation): bool => $operation === 'create'), // يظهر فقط في وضع الإنشاء لتأكيد التعبئة التلقائية

                Forms\Components\Placeholder::make('project_info')
                    ->content(fn (?Task $record, string $operation) => $operation === 'create' ? $this->getOwnerRecord()->name : ($record?->project->name ?? 'غير محدد'))
                    ->label('المشروع التابع له المهمة')
                    ->columnSpanFull(), // ليأخذ عرضاً كاملاً في النموذج

                Forms\Components\Select::make('workshop_id')
                    // تصفية الورش بناءً على المشروع الحالي (المشروع الأب للـ Relation Manager)
                    ->relationship('workshop', 'name', fn (Builder $query) => $query->where('project_id', $this->getOwnerRecord()->id))
                    ->getOptionLabelFromRecordUsing(fn (Workshop $record) => $record->name)
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
                            return $user->hasRole('Worker') ||
                                   $user->hasRole('Architectural Engineer') ||
                                   $user->hasRole('Civil Engineer') ||
                                   $user->hasRole('Structural Engineer') ||
                                   $user->hasRole('Electrical Engineer') ||
                                   $user->hasRole('Mechanical Engineer') ||
                                   $user->hasRole('Geotechnical Engineer') ||
                                   $user->hasRole('Quantity Surveyor') ||
                                   $user->hasRole('Site Engineer') ||
                                   $user->hasRole('Environmental Engineer') ||
                                   $user->hasRole('Surveying Engineer');
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50),
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
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}