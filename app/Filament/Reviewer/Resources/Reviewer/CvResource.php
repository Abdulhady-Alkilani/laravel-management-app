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
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Actions\Action;
use App\Models\User;
use App\Services\AiCvScoringService;
use Filament\Notifications\Notification;

class CvResource extends Resource
{
    protected static ?string $model = Cv::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'مراجعة السير الذاتية';
    protected static ?string $pluralModelLabel = 'السير الذاتية للمراجعة';
    protected static ?string $modelLabel = 'سيرة ذاتية';

    protected static bool $canCreate = false;
    protected static bool $canEdit = true;
    protected static bool $canDelete = false;

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
                Forms\Components\Section::make('بيانات صاحب السيرة')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Placeholder::make('user_info')
                            ->content(fn (Cv $record) => $record->user->name . ' (' . $record->user->email . ')')
                            ->label('صاحب السيرة الذاتية'),
                    ]),

                Forms\Components\Section::make('تفاصيل السيرة الذاتية')
                    ->icon('heroicon-o-document-text')
                    ->description('البيانات التالية للعرض فقط ولا يمكن تعديلها')
                    ->schema([
                        Forms\Components\Textarea::make('profile_details')
                            ->columnSpanFull()
                            ->label('تفاصيل الملف الشخصي')
                            ->rows(3)
                            ->disabled(),
                        Forms\Components\Textarea::make('experience')
                            ->columnSpanFull()
                            ->label('الخبرات')
                            ->rows(4)
                            ->disabled(),
                        Forms\Components\Textarea::make('education')
                            ->columnSpanFull()
                            ->label('المؤهلات العلمية')
                            ->rows(3)
                            ->disabled(),
                    ]),

                Forms\Components\Section::make('ملف السيرة الذاتية')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Forms\Components\FileUpload::make('cv_file_path')
                            ->label('ملف السيرة الذاتية')
                            ->disk('public')
                            ->directory('cvs')
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->columnSpanFull()
                            ->disabled()
                            ->helperText('ملف السيرة الذاتية المرفق (للعرض والتنزيل فقط)'),
                        // زر فتح الملف في تبويبة جديدة
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('open_cv_file')
                                ->label('فتح ملف CV في تبويبة جديدة')
                                ->icon('heroicon-o-arrow-top-right-on-square')
                                ->color('info')
                                ->url(fn (?Cv $record) => $record && $record->cv_file_path
                                    ? Storage::disk('public')->url($record->cv_file_path)
                                    : null)
                                ->openUrlInNewTab()
                                ->visible(fn (?Cv $record) => $record && $record->cv_file_path),
                        ]),
                    ]),

                Forms\Components\Section::make('المهارات')
                    ->icon('heroicon-o-light-bulb')
                    ->schema([
                        Forms\Components\Select::make('skills_list')
                            ->multiple()
                            ->relationship('skills', 'name')
                            ->preload()
                            ->disabled()
                            ->label('المهارات'),
                    ]),

                Forms\Components\Section::make('تقييم الذكاء الاصطناعي')
                    ->icon('heroicon-o-cpu-chip')
                    ->schema([
                        Forms\Components\Placeholder::make('ai_score_display')
                            ->label('درجة التقييم')
                            ->content(fn (?Cv $record) => $record && $record->ai_score !== null
                                ? "{$record->ai_score}/100"
                                : 'غير مقيّم بعد'),
                    ])
                    ->visible(fn (?Cv $record) => $record && $record->ai_score !== null),

                Forms\Components\Section::make('قرار المراجعة')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Forms\Components\Select::make('cv_status')
                            ->options([
                                'تمت الموافقة' => 'تمت الموافقة',
                                'مرفوض' => 'مرفوض',
                                'قيد الانتظار' => 'قيد الانتظار',
                            ])
                            ->required()
                            ->label('تغيير حالة السيرة الذاتية'),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->columnSpanFull()
                            ->nullable()
                            ->rows(4)
                            ->label('تعليقات المراجعة / سبب الرفض')
                            ->helperText('يمكنك إضافة تعليق للمراجعة أو سبب الرفض هنا.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('صاحب السيرة')
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('user', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
                    )
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('experience')
                    ->label('الخبرة')
                    ->limit(40)
                    ->tooltip(fn ($state) => $state)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('education')
                    ->label('التعليم')
                    ->limit(40)
                    ->tooltip(fn ($state) => $state)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('skills.name')
                    ->label('المهارات')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ai_score')
                    ->label('تقييم AI')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state !== null ? "{$state}/100" : 'غير مقيّم')
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->label('التعليقات/سبب الرفض')
                    ->limit(40)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i')
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
                    ->default('قيد الانتظار')
                    ->label('حالة السيرة الذاتية'),
                Tables\Filters\Filter::make('has_cv_file')
                    ->label('يحتوي على ملف CV')
                    ->query(fn (Builder $query) => $query->whereNotNull('cv_file_path')->where('cv_file_path', '!=', '')),
                Tables\Filters\Filter::make('ai_scored')
                    ->label('تم تقييمه بالذكاء الاصطناعي')
                    ->query(fn (Builder $query) => $query->whereNotNull('ai_score')),
            ])
            ->actions([
                // زر فتح ملف CV في تبويبة جديدة
                Action::make('open_cv')
                    ->label('عرض CV')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->url(fn (Cv $record) => $record->cv_file_path
                        ? Storage::disk('public')->url($record->cv_file_path)
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn (Cv $record) => filled($record->cv_file_path)),
                // زر تحميل ملف CV
                Action::make('download_cv')
                    ->label('تحميل')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function (Cv $record) {
                        if ($record->cv_file_path && Storage::disk('public')->exists($record->cv_file_path)) {
                            return response()->download(Storage::disk('public')->path($record->cv_file_path));
                        }
                        Notification::make()->title('الملف غير موجود')->danger()->send();
                    })
                    ->visible(fn (Cv $record) => filled($record->cv_file_path)),
                Action::make('ai_analyze')
                    ->label('تحليل AI')
                    ->icon('heroicon-o-cpu-chip')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('تحليل السيرة الذاتية بالذكاء الاصطناعي')
                    ->modalDescription('سيتم إرسال بيانات هذه السيرة الذاتية إلى خدمة الذكاء الاصطناعي لتقييمها.')
                    ->action(function (Cv $record) {
                        $service = new AiCvScoringService();
                        $cvData = [
                            'skills' => $record->skills->pluck('name')->implode(', '),
                            'experience' => $record->experience,
                            'education' => $record->education,
                            'profile_details' => $record->profile_details,
                            'cv_file_path' => $record->cv_file_path,
                        ];
                        $result = $service->scoreCv($cvData);
                        if ($result !== null) {
                            $oldReason = $record->rejection_reason ?? '';
                            if (trim($oldReason) === 'لم يتم تقديم تعليق' || trim($oldReason) === 'لا يوجد') {
                                $oldReason = '';
                            }
                            $newReason = $result['reason'] . ($oldReason ? "\n\n" . $oldReason : '');
                            
                            $record->update([
                                'ai_score' => $result['score'],
                                'rejection_reason' => trim($newReason),
                            ]);
                            Notification::make()
                                ->title('تم التقييم بنجاح')
                                ->body("الدرجة: {$result['score']}/100")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('فشل التقييم')
                                ->body('تأكد من إعداد API Key أو أنك لم تتجاوز حد الاستخدام لخدمة الذكاء الاصطناعي (Quota).')
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('approve')
                    ->label('موافقة')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->nullable()
                            ->label('تعليقات المراجعة')
                            ->helperText('أضف أي تعليقات عند الموافقة.'),
                    ])
                    ->action(function (Cv $record, array $data) {
                        $record->cv_status = 'تمت الموافقة';
                        $record->rejection_reason = $data['rejection_reason'];
                        $record->save();
                        Notification::make()->title('تمت الموافقة على السيرة الذاتية')->success()->send();
                    })
                    ->visible(fn (Cv $record) => $record->cv_status === 'قيد الانتظار'),
                Action::make('reject')
                    ->label('رفض')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->label('سبب الرفض')
                            ->helperText('الرجاء توضيح سبب الرفض.'),
                    ])
                    ->action(function (Cv $record, array $data) {
                        $record->cv_status = 'مرفوض';
                        $record->rejection_reason = $data['rejection_reason'];
                        $record->save();
                        Notification::make()->title('تم رفض السيرة الذاتية')->warning()->send();
                    })
                    ->visible(fn (Cv $record) => $record->cv_status === 'قيد الانتظار'),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('بيانات صاحب السيرة')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('الاسم'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('البريد الإلكتروني'),
                    ]),

                Infolists\Components\Section::make('تفاصيل السيرة الذاتية')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Infolists\Components\TextEntry::make('profile_details')
                            ->label('تفاصيل الملف الشخصي')
                            ->default('غير محدد')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('experience')
                            ->label('الخبرات')
                            ->default('غير محدد')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('education')
                            ->label('المؤهلات العلمية')
                            ->default('غير محدد')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('skills.name')
                            ->label('المهارات')
                            ->badge()
                            ->color('primary'),
                    ]),

                Infolists\Components\Section::make('ملف السيرة الذاتية')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('view_cv_file')
                                ->label('عرض ملف CV في تبويبة جديدة')
                                ->icon('heroicon-o-eye')
                                ->color('success')
                                ->url(fn (Cv $record) => $record->cv_file_path
                                    ? Storage::disk('public')->url($record->cv_file_path)
                                    : null)
                                ->openUrlInNewTab()
                                ->visible(fn (Cv $record) => filled($record->cv_file_path)),
                            Infolists\Components\Actions\Action::make('download_cv_file')
                                ->label('تحميل ملف CV')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->color('info')
                                ->action(function (Cv $record) {
                                    if ($record->cv_file_path && Storage::disk('public')->exists($record->cv_file_path)) {
                                        return response()->download(Storage::disk('public')->path($record->cv_file_path));
                                    }
                                })
                                ->visible(fn (Cv $record) => filled($record->cv_file_path)),
                        ]),
                        Infolists\Components\TextEntry::make('cv_file_path')
                            ->label('حالة الملف')
                            ->formatStateUsing(fn ($state) => $state ? 'ملف مرفق ✅' : 'لا يوجد ملف ❌')
                            ->color(fn ($state) => $state ? 'success' : 'danger'),
                    ]),

                Infolists\Components\Section::make('الحالة والتقييم')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('cv_status')
                            ->label('حالة السيرة الذاتية')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'قيد الانتظار' => 'warning',
                                'تمت الموافقة' => 'success',
                                'مرفوض' => 'danger',
                                default => 'secondary',
                            }),
                        Infolists\Components\TextEntry::make('ai_score')
                            ->label('تقييم الذكاء الاصطناعي')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state !== null ? "{$state}/100" : 'غير مقيّم')
                            ->color(fn ($state) => match (true) {
                                $state === null => 'gray',
                                $state >= 80 => 'success',
                                $state >= 50 => 'warning',
                                default => 'danger',
                            }),
                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->label('التعليقات / سبب الرفض')
                            ->default('لا يوجد')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ التقديم')
                            ->dateTime('Y-m-d H:i'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('Y-m-d H:i'),
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
            'view' => Pages\ViewCv::route('/{record}'),
            'edit' => Pages\EditCv::route('/{record}/edit'),
        ];
    }
}