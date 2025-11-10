<?php

namespace App\Filament\Reviewer\Resources\Reviewer;

use App\Filament\Reviewer\Resources\Reviewer\CvResource\Pages;
use App\Filament\Reviewer\Resources\Reviewer\CvResource\RelationManagers;
use App\Models\Cv;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;

class CvResource extends Resource
{
    protected static ?string $model = Cv::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'مراجعة السير الذاتية';
    protected static ?string $pluralModelLabel = 'السير الذاتية للمراجعة';
    protected static ?string $modelLabel = 'سيرة ذاتية';

    // <== تعطيل صلاحيات الإنشاء والحذف بالكامل
    protected static bool $canCreate = false;
    // <== تمكين صلاحية التعديل
    protected static bool $canEdit = true;
    protected static bool $canDelete = false;

    // <== تصفية السير الذاتية التي تحتاج للمراجعة
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('cv_status', ['قيد الانتظار', 'تمت الموافقة', 'مرفوض'])
            ->orderByDesc('created_at');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('user_info')
                    ->content(fn (Cv $record) => $record->user->name . ' (' . $record->user->email . ')')
                    ->label('صاحب السيرة الذاتية'),
                Forms\Components\Textarea::make('profile_details')
                    ->columnSpanFull()
                    ->label('تفاصيل الملف الشخصي')
                    ->disabled(), // <== للقراءة فقط
                Forms\Components\Textarea::make('experience')
                    ->columnSpanFull()
                    ->label('الخبرات')
                    ->disabled(), // <== للقراءة فقط
                Forms\Components\Textarea::make('education')
                    ->columnSpanFull()
                    ->label('المؤهلات العلمية')
                    ->disabled(), // <== للقراءة فقط
                Forms\Components\Select::make('skills_list')
                    ->multiple()
                    ->relationship('skills', 'name')
                    ->preload()
                    ->disabled() // <== للقراءة فقط
                    ->label('المهارات'),
                // حقول المراجعة القابلة للتعديل
                Forms\Components\Select::make('cv_status')
                    ->options([
                        'تمت الموافقة' => 'تمت الموافقة',
                        'مرفوض' => 'مرفوض',
                        'قيد الانتظار' => 'قيد الانتظار',
                    ])
                    ->required()
                    ->label('تغيير حالة السيرة الذاتية'),
                Forms\Components\Textarea::make('rejection_reason') // <== حقل التعليقات/السبب يظهر هنا
                    ->columnSpanFull()
                    ->nullable()
                    ->label('تعليقات المراجعة / سبب الرفض')
                    ->helperText('يمكنك إضافة تعليق للمراجعة أو سبب الرفض هنا.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('صاحب السيرة'),
                Tables\Columns\TextColumn::make('experience')
                    ->label('الخبرة')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('education')
                    ->label('التعليم')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('cv_status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'قيد الانتظار' => 'warning',
                        'تمت الموافقة' => 'success',
                        'مرفوض' => 'danger',
                        default => 'secondary',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('skills.name')
                    ->label('المهارات')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->label('التعليقات/سبب الرفض')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ التقديم'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cv_status')
                    ->options([
                        'قيد الانتظار' => 'قيد الانتظار',
                        'تمت الموافقة' => 'تمت الموافقة',
                        'مرفوض' => 'مرفوض',
                    ])
                    ->label('حالة السيرة الذاتية'),
            ])
            ->actions([
                // إجراءات الموافقة/الرفض تفتح نموذجًا لإدخال التعليقات مباشرة
                Action::make('approve')
                    ->label('موافقة')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason') // <== حقل تعليق الموافقة
                            ->nullable()
                            ->label('تعليقات المراجعة')
                            ->helperText('أضف أي تعليقات عند الموافقة.'),
                    ])
                    ->action(function (Cv $record, array $data) {
                        $record->cv_status = 'تمت الموافقة';
                        $record->rejection_reason = $data['rejection_reason']; // يستخدم لتعليقات الموافقة
                        $record->save();
                    })
                    ->visible(fn (Cv $record) => $record->cv_status === 'قيد الانتظار'),
                Action::make('reject')
                    ->label('رفض')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason') // <== حقل سبب الرفض
                            ->required()
                            ->label('سبب الرفض')
                            ->helperText('الرجاء توضيح سبب الرفض.'),
                    ])
                    ->action(function (Cv $record, array $data) {
                        $record->cv_status = 'مرفوض';
                        $record->rejection_reason = $data['rejection_reason'];
                        $record->save();
                    })
                    ->visible(fn (Cv $record) => $record->cv_status === 'قيد الانتظار'),
                Tables\Actions\ViewAction::make(), // <== عرض التفاصيل
                Tables\Actions\EditAction::make(), // <== للوصول إلى نموذج التعديل الكامل
            ])
            ->bulkActions([
                // لا توجد إجراءات مجمعة في هذه الحالة
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SkillsRelationManager::class, // <== ستكون للقراءة فقط
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCvs::route('/'),
            'view' => Pages\ViewCv::route('/{record}'),
            'edit' => Pages\EditCv::route('/{record}/edit'), // <== تفعيل صفحة التعديل
        ];
    }
}