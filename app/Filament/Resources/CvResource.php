<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CvResource\Pages;
use App\Filament\Resources\CvResource\RelationManagers;
use App\Models\Cv;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope; // هذا الاستيراد قد لا يكون مستخدماً إذا لم يكن لديك soft deletes
use App\Models\User; // <== تأكد من استيراد User model

class CvResource extends Resource
{
    protected static ?string $model = Cv::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'السير الذاتية';
    protected static ?string $pluralModelLabel = 'السير الذاتية';
    protected static ?string $modelLabel = 'سيرة ذاتية';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'first_name') // عرض الاسم الأول للمستخدم
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('المستخدم')
                    ->disabledOn('edit'), // <== التعديل الرئيسي هنا: تعطيل الحقل عند التعديل
                Forms\Components\Textarea::make('profile_details')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('تفاصيل الملف الشخصي'),
                Forms\Components\Textarea::make('experience')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('الخبرات'),
                Forms\Components\Textarea::make('education')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('المؤهلات العلمية'),
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
                    ->label('سبب الرفض'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name') // <== استخدام user.name accessor
                    ->label('صاحب السيرة')
                    // <== التعديل الرئيسي هنا: استخدام استعلام مخصص للبحث
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('user', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
            ),
                    // ->sortable(),
                Tables\Columns\TextColumn::make('experience')
                    ->label('الخبرة')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('education')
                    ->label('التعليم')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('cv_status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'تحتاج تأكيد' => 'warning',
                        'تمت الموافقة' => 'success',
                        'قيد الانتظار' => 'info',
                        'مرفوض' => 'danger',
                        default => 'secondary',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->label('سبب الرفض')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('skills.name') // عرض المهارات المرتبطة
                    ->label('المهارات')
                    ->badge()
                    // <== إضافة searchable() مخصص هنا إذا كانت skills.name تسبب مشكلة
                    ->searchable(query: fn (Builder $query, string $search) =>
                        $query->whereHas('skills', fn (Builder $subQuery) =>
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ الإنشاء'),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\SkillsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCvs::route('/'),
            'create' => Pages\CreateCv::route('/create'),
            'edit' => Pages\EditCv::route('/{record}/edit'),
        ];
    }
}