<?php
namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CvsRelationManager extends RelationManager
{
    protected static string $relationship = 'cvs'; // اسم العلاقة في User model

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('profile_details')->columnSpanFull()->nullable()->label('تفاصيل الملف الشخصي'),
                Forms\Components\Textarea::make('experience')->columnSpanFull()->nullable()->label('الخبرات'),
                Forms\Components\Textarea::make('education')->columnSpanFull()->nullable()->label('التعليم'),
                Forms\Components\Select::make('cv_status')
                    ->options([
                        'تحتاج تأكيد' => 'تحتاج تأكيد',
                        'تمت الموافقة' => 'تمت الموافقة',
                        'قيد الانتظار' => 'قيد الانتظار',
                        'مرفوض' => 'مرفوض',
                    ])
                    ->required()
                    ->label('حالة السيرة الذاتية'),
                Forms\Components\Textarea::make('rejection_reason')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('سبب الرفض (إن وجد)'),
                // ملاحظة: المهارات هي علاقة BelongsToMany لـ Cv، يمكن إدارتها عبر CvResource مباشرةً
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('profile_details')
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')->label('صاحب السيرة')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('experience')->label('الخبرة')->searchable(),
                Tables\Columns\TextColumn::make('education')->label('التعليم')->searchable(),
                Tables\Columns\TextColumn::make('cv_status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'تحتاج تأكيد' => 'warning',
                        'تمت الموافقة' => 'success',
                        'قيد الانتظار' => 'info',
                        'مرفوض' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('skills.name')->label('المهارات')->badge(), // عرض المهارات
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