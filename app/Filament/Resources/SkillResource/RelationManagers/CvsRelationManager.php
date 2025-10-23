<?php

namespace App\Filament\Resources\SkillResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CvsRelationManager extends RelationManager
{
    protected static string $relationship = 'cvs'; // اسم العلاقة في Skill model

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // لا يوجد نموذج لإنشاء سيرة ذاتية هنا، بل لربط سير ذاتية موجودة أو عرضها
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.first_name') // يعرض اسم صاحب السيرة الذاتية
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')->label('الاسم الأول'),
                Tables\Columns\TextColumn::make('user.last_name')->label('الاسم الأخير'),
                Tables\Columns\TextColumn::make('experience')->label('الخبرة')->limit(50),
                Tables\Columns\TextColumn::make('cv_status')->label('الحالة')->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make() // لربط سير ذاتية موجودة بالمهارة
                    ->preloadRecordSelect()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(), // لفصل السيرة الذاتية عن المهارة
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}