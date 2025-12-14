<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';
    protected static ?string $title = 'المهام التابعة';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('workshop_id') // هنا نختار الورشة ضمن المشروع
                    ->relationship('workshop', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
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
                    ->default(0)
                    ->label('التقدم (%)'),
                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->label('تاريخ البدء المخطط'),
                Forms\Components\DatePicker::make('end_date_planned')
                    ->required()
                    ->label('تاريخ الانتهاء المخطط'),
                Forms\Components\DatePicker::make('actual_end_date')
                    ->nullable()
                    ->label('تاريخ الانتهاء الفعلي'),
                Forms\Components\Select::make('assigned_to_user_id')
                    ->relationship('assignedTo', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('العامل المسؤول'),
                Forms\Components\Select::make('status')
                    ->options([
                        'لم تبدأ' => 'لم تبدأ',
                        'قيد التنفيذ' => 'قيد التنفيذ',
                        'مكتملة' => 'مكتملة',
                        'متوقفة' => 'متوقفة',
                    ])
                    ->required()
                    ->default('لم تبدأ')
                    ->label('الحالة'),
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
                    ->searchable()
                    ->html()
                    ->limit(50)
                    ->label('الوصف'),
                Tables\Columns\TextColumn::make('workshop.name')
                    ->label('الورشة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedTo.first_name')
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