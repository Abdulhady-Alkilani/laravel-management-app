<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';
    protected static ?string $title = 'التقارير';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('الموظف مقدم التقرير'),
                Forms\Components\Select::make('workshop_id')
                    ->relationship('workshop', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('الورشة'),
                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('الخدمة'),
                Forms\Components\TextInput::make('report_type')
                    ->required()
                    ->maxLength(255)
                    ->label('نوع التقرير'),
                RichEditor::make('report_details')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('تفاصيل التقرير'),
                Forms\Components\TextInput::make('report_status')
                    ->maxLength(255)
                    ->nullable()
                    ->label('حالة التقرير'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('report_type')
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')->label('الموظف')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('workshop.name')->label('الورشة')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('service.name')->label('الخدمة')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('report_type')->label('نوع التقرير')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('report_status')->label('الحالة')->badge()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الإنشاء')->dateTime()->sortable(),
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