<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class WorkshopsRelationManager extends RelationManager
{
    protected static string $relationship = 'workshops'; // اسم العلاقة في Project model
    protected static ?string $title = 'الورشات التابعة';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('اسم الورشة'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('وصف الورشة'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('اسم الورشة')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('description')->label('الوصف')->limit(50)->html(),
                Tables\Columns\TextColumn::make('workers_count')->counts('workers')->label('عدد العمال'),
                Tables\Columns\TextColumn::make('tasks_count')->counts('tasks')->label('عدد المهام'),
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