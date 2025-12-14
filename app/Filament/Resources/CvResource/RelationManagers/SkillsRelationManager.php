<?php
namespace App\Filament\Resources\CvResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SkillsRelationManager extends RelationManager
{
    protected static string $relationship = 'skills'; // اسم العلاقة في Cv model

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->label('اسم المهارة'),
                Forms\Components\Textarea::make('description')
                    ->nullable()
                    ->columnSpanFull()
                    ->label('وصف المهارة'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('اسم المهارة')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('description')->label('الوصف')->limit(50)->html(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make(), // لإرفاق مهارة موجودة
                Tables\Actions\CreateAction::make(), // لإنشاء مهارة جديدة وربطها
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(), // لفصل المهارة عن السيرة الذاتية
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}