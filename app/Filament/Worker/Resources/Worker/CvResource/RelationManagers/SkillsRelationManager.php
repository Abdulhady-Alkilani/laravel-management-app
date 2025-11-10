<?php

namespace App\Filament\Worker\Resources\Worker\CvResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Skill;

class SkillsRelationManager extends RelationManager
{
    protected static string $relationship = 'skills';
    protected static ?string $title = 'المهارات';
    protected static ?string $pluralTitle = 'المهارات';
    protected static ?string $modelLabel = 'مهارة';
    protected static ?string $pluralModelLabel = 'مهارات';


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
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المهارة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make(), // لربط مهارة موجودة
                Tables\Actions\CreateAction::make(), // لإنشاء مهارة جديدة وربطها
            ])
            ->actions([
               // Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                // Tables\Actions\DeleteAction::make(), // لا يمكن للعامل حذف المهارات من النظام
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}