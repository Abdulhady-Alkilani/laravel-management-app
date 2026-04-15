<?php

namespace App\Filament\Engineer\Resources\Engineer;

use App\Filament\Engineer\Resources\Engineer\CvResource\Pages;
use App\Filament\Engineer\Resources\Engineer\CvResource\RelationManagers;
use App\Models\Cv;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\Skill;

class CvResource extends Resource
{
    protected static ?string $model = Cv::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'ملفي الشخصي/CV';
    protected static ?string $pluralModelLabel = 'ملفي الشخصي/CV';
    protected static ?string $modelLabel = 'سيرتي الذاتية';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    // <== تعطيل الإنشاء والحذف، وتمكين التعديل
    protected static bool $canCreate = false;
    protected static bool $canEdit = true;
    protected static bool $canDelete = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('profile_details')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('نبذة عني (تفاصيل الملف الشخصي)'),
                Forms\Components\Textarea::make('experience')
                    ->columnSpanFull()
                    ->required()
                    ->label('الخبرات'),
                Forms\Components\Textarea::make('education')
                    ->columnSpanFull()
                    ->required()
                    ->label('المؤهلات العلمية'),
                Forms\Components\FileUpload::make('cv_file_path')
                    ->label('ملف السيرة الذاتية')
                    ->disk('public')
                    ->directory('cvs')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->maxSize(5120)
                    ->openable()
                    ->downloadable()
                    ->previewable()
                    ->columnSpanFull()
                    ->helperText('ارفع سيرتك الذاتية (PDF, JPG, PNG — الحد الأقصى: 5 ميجابايت)'),
                Forms\Components\Placeholder::make('cv_status_info')
                    ->content(fn (Cv $record) => $record->cv_status)
                    ->label('حالة السيرة الذاتية'),
                Forms\Components\Placeholder::make('rejection_reason_info')
                    ->content(fn (Cv $record) => $record->rejection_reason ?? 'لا يوجد')
                    ->label('سبب الرفض (إن وجد)')
                    ->visible(fn (Cv $record) => filled($record->rejection_reason)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('صاحب السيرة'),
                Tables\Columns\TextColumn::make('experience')
                    ->label('الخبرة')
                    ->limit(50),
                Tables\Columns\TextColumn::make('education')
                    ->label('التعليم')
                    ->limit(50),
                Tables\Columns\IconColumn::make('cv_file_path')
                    ->label('ملف CV')
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-arrow-down' : 'heroicon-o-x-mark')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->tooltip(fn ($state) => $state ? 'انقر لعرض الملف' : 'لا يوجد ملف')
                    ->action(fn (Cv $record) => $record->cv_file_path ? response()->download(storage_path('app/public/' . $record->cv_file_path)) : null),
                Tables\Columns\TextColumn::make('cv_status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'قيد الانتظار' => 'warning', 'تمت الموافقة' => 'success',
                        'مرفوض' => 'danger', default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('skills.name')
                    ->label('المهارات')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // <== زر العرض
                Tables\Actions\EditAction::make(), // <== زر التعديل
            ])
            ->bulkActions([
                // <== لا توجد إجراءات مجمعة
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SkillsRelationManager::class, // <== سيتم تعديلها لاحقاً
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCvs::route('/'),
            // 'create' => Pages\CreateCv::route('/create'), // <== تعطيل صفحة الإنشاء
            'edit' => Pages\EditCv::route('/{record}/edit'),
            'view' => Pages\ViewCv::route('/{record}'),
        ];
    }
}