<?php

namespace App\Filament\Manager\Resources\Manager\WorkshopResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use App\Models\User;
use App\Models\Role;
use Closure;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks'; // اسم العلاقة في Workshop model
    protected static ?string $title = 'المهام';
    protected static ?string $pluralTitle = 'المهام';
    protected static ?string $modelLabel = 'مهمة';
    protected static ?string $pluralModelLabel = 'مهام';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // workshop_id يتم ربطه تلقائيا بالورشة الأب
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name') // العلاقة مع project
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('المشروع')
                    ->helperText('المشروع الذي تنتمي إليه هذه المهمة.'),
                RichEditor::make('description')
                    ->required()
                    ->columnSpanFull()
                    ->label('وصف المهمة')
                    ->helperText('وصف تفصيلي للمهمة المطلوبة.'),
                Forms\Components\TextInput::make('progress')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required()
                    ->live()
                    ->label('التقدم (%)')
                    ->helperText('نسبة إنجاز المهمة (0-100%).'),
                Forms\Components\Select::make('status')
                    ->options([
                        'لم تبدأ' => 'لم تبدأ',
                        'قيد التنفيذ' => 'قيد التنفيذ',
                        'مكتملة' => 'مكتملة',
                        'متوقفة' => 'متوقفة',
                    ])
                    ->required()
                    ->label('الحالة')
                    ->helperText('الحالة الحالية للمهمة.')
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
                    ->label('تاريخ البدء المخطط')
                    ->helperText('التاريخ المتوقع لبدء المهمة.'),
                Forms\Components\DatePicker::make('end_date_planned')
                    ->required()
                    ->label('تاريخ الانتهاء المخطط')
                    ->helperText('التاريخ المتوقع لانتهاء المهمة.'),
                Forms\Components\DatePicker::make('actual_end_date')
                    ->nullable()
                    ->label('تاريخ الانتهاء الفعلي')
                    ->helperText('التاريخ الفعلي الذي تم فيه إنجاز المهمة.')
                    ->visible(fn (Forms\Get $get) => $get('status') === 'مكتملة'),
                Forms\Components\Select::make('assigned_to_user_id')
                    ->relationship('assignedTo', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('العامل المسؤول')
                    ->helperText('اختر العامل أو المهندس المسؤول عن هذه المهمة.')
                    // تصفية العمال/المهندسين
                    ->getSearchResultsUsing(fn (string $search) => User::whereHas('roles', fn ($query) => $query->whereIn('name', ['Worker', 'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer', 'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer', 'Environmental Engineer', 'Surveying Engineer']))
                        ->where(fn ($query) => $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->limit(50)
                        ->get())
                    ->getOptionLabelUsing(fn (User $record): ?string => "{$record->first_name} {$record->last_name}"),
                Forms\Components\TextInput::make('estimated_cost')
                    ->numeric()
                    ->nullable()
                    ->prefix('SYP')
                    ->label('التكلفة التقديرية')
                    ->helperText('التكلفة المتوقعة لإنجاز المهمة.'),
                Forms\Components\TextInput::make('actual_cost')
                    ->numeric()
                    ->nullable()
                    ->prefix('SYP')
                    ->label('التكلفة الفعلية')
                    ->helperText('التكلفة الفعلية التي تم صرفها على المهمة.'),
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
                Tables\Columns\TextColumn::make('project.name')
                    ->label('المشروع')
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