<?php

namespace App\Filament\Engineer\Resources\Engineer\ProjectResource\RelationManagers;

use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Auth;
use Closure; // <== استيراد Closure

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';
    protected static ?string $title = 'المهام الخاصة بي في هذا المشروع';
    protected static ?string $pluralTitle = 'المهام الخاصة بي في هذا المشروع';
    protected static ?string $modelLabel = 'مهمة';
    protected static ?string $pluralModelLabel = 'مهام';

    public function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('assigned_to_user_id', Auth::id());
    }

    protected static bool $canCreate = false;
    protected static bool $canEdit = true;
    protected static bool $canDelete = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('description_info')
                    ->content(fn (Task $record) => $record->description)
                    ->label('وصف المهمة')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('progress')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required()
                    ->live() // <== أضف live()
                    ->label('التقدم (%)'),
                Forms\Components\Select::make('status')
                    ->options([
                        'لم تبدأ' => 'لم تبدأ',
                        'قيد التنفيذ' => 'قيد التنفيذ',
                        'مكتملة' => 'مكتملة',
                        'متوقفة' => 'متوقفة',
                    ])
                    ->required()
                    ->label('الحالة')
                    // <== هنا التعديل: إضافة قاعدة التحقق المنطقية
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
                Forms\Components\Placeholder::make('start_date_info')
                    ->content(fn (Task $record) => $record->start_date->format('Y-m-d'))
                    ->label('تاريخ البدء المخطط'),
                Forms\Components\Placeholder::make('end_date_planned_info')
                    ->content(fn (Task $record) => $record->end_date_planned->format('Y-m-d'))
                    ->label('تاريخ الانتهاء المخطط'),
                Forms\Components\DatePicker::make('actual_end_date')
                    ->nullable()
                    ->label('تاريخ الانتهاء الفعلي')
                    ->visible(fn (Forms\Get $get) => $get('status') === 'مكتملة'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->html()
                    ->limit(50),
                Tables\Columns\TextColumn::make('progress')
                    ->label('التقدم (%)'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                Tables\Columns\TextColumn::make('end_date_planned')
                    ->label('تاريخ الانتهاء')
                    ->date(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}