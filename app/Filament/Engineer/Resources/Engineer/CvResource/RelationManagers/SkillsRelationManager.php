<?php

namespace App\Filament\Engineer\Resources\Engineer\CvResource\RelationManagers;

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

    // <== تعطيل صلاحيات التعديل والحذف
    protected static bool $canCreate = true; // يمكن إنشاء مهارة جديدة
    protected static bool $canEdit = false;
    protected static bool $canDelete = false;

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
                Tables\Actions\AttachAction::make(), // <== لإرفاق مهارة موجودة
                Tables\Actions\CreateAction::make(), // <== لإضافة مهارة جديدة للنظام وربطها بالـ CV
            ])
            ->actions([
                Tables\Actions\DetachAction::make(), // <== لفك ارتباط المهارة من الـ CV
                // لا يمكن للمهندس تعديل أو حذف المهارات من النظام
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(), // <== لفك ارتباط مهارات متعددة
                ]),
            ]);
    }
}