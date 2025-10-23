<?php

namespace App\Filament\Resources\RoleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users'; // اسم العلاقة في Role model

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // لا يوجد نموذج لإنشاء مستخدم هنا، بل لربط مستخدمين موجودين أو عرضهم
                // يمكنك إضافة حقول لتعديل معلومات الربط إذا كانت العلاقة تحتوي على pivot data
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name') // اسم المستخدم الكامل أو البريد الإلكتروني
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->label('الاسم الأول')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')->label('الاسم الأخير')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('البريد الإلكتروني')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('username')->label('اسم المستخدم')->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make() // لربط مستخدمين موجودين بالدور
                    ->preloadRecordSelect() // لتحميل خيارات المستخدمين مسبقاً
                    ->searchable(), // لتمكين البحث في قائمة المستخدمين المتاحين للربط
                // Tables\Actions\CreateAction::make(), // لا ننشئ مستخدمين من هنا، بل نربط موجودين
            ])
            ->actions([
                Tables\Actions\DetachAction::make(), // لفصل المستخدم عن الدور
                // Tables\Actions\DeleteAction::make(), // لا نحذف المستخدم من هنا
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}