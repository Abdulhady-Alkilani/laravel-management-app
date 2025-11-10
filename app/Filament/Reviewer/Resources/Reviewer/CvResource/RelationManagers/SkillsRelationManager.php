<?php

namespace App\Filament\Reviewer\Resources\Reviewer\CvResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SkillsRelationManager extends RelationManager
{
    protected static string $relationship = 'skills';
    protected static ?string $title = 'المهارات المرتبطة';
    protected static ?string $pluralTitle = 'المهارات المرتبطة';
    protected static ?string $modelLabel = 'مهارة';
    protected static ?string $pluralModelLabel = 'مهارات';

    // <== تعطيل صلاحيات الإنشاء والتعديل والحذف في Relation Manager
    protected static bool $canCreate = false;
    protected static bool $canEdit = false;
    protected static bool $canDelete = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم المهارة')
                    ->disabled(), // <== للقراءة فقط
                Forms\Components\Textarea::make('description')
                    ->label('وصف المهارة')
                    ->disabled(), // <== للقراءة فقط
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
            ->actions([
                Tables\Actions\ViewAction::make(), // <== فقط عرض التفاصيل
                // لا يمكن للمراجع التعديل أو الفصل أو الحذف
            ])
            ->bulkActions([
                // لا توجد إجراءات مجمعة
            ]);
    }
}