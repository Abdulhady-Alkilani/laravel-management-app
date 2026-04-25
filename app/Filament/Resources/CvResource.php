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
use Filament\Tables\Actions\Action;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use App\Models\Role;
use App\Services\AiCvScoringService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class CvResource extends Resource
{
    protected static ?string $model = Cv::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'السير الذاتية';
    protected static ?string $pluralModelLabel = 'السير الذاتية';
    protected static ?string $modelLabel = 'سيرة ذاتية';

    public static function form(Form $form): Form
    {
        $engineerAndWorkerRolesNames = [
            'Worker',
            'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
            'Environmental Engineer', 'Surveying Engineer', 'Information Technology Engineer', 'Telecommunications Engineer',
        ];

        return $form
            ->schema([
                Forms\Components\Section::make('بيانات المستخدم')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'first_name', fn (Builder $query) =>
                                $query->whereHas('roles', fn (Builder $roleQuery) =>
                                    $roleQuery->whereIn('name', $engineerAndWorkerRolesNames)
                                )
                                ->whereDoesntHave('cvs') 
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('المستخدم')
                            ->disabledOn('edit'),
                    ]),

                Forms\Components\Section::make('تفاصيل السيرة الذاتية')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('profile_details')
                            ->columnSpanFull()
                            ->nullable()
                            ->rows(3)
                            ->label('تفاصيل الملف الشخصي'),
                        Forms\Components\Textarea::make('experience')
                            ->columnSpanFull()
                            ->nullable()
                            ->rows(4)
                            ->label('الخبرات'),
                        Forms\Components\Textarea::make('education')
                            ->columnSpanFull()
                            ->nullable()
                            ->rows(3)
                            ->label('المؤهلات العلمية'),
                    ]),

                Forms\Components\Section::make('ملف السيرة الذاتية')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Forms\Components\FileUpload::make('cv_file_path')
                            ->label('ملف السيرة الذاتية')
                            ->disk('public')
                            ->directory('cvs')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(5120) // 5MB
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->columnSpanFull()
                            ->helperText('الأنواع المسموحة: PDF, JPG, PNG — الحد الأقصى: 5 ميجابايت'),
                        // زر فتح الملف في تبويبة جديدة عند التعديل
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

                Forms\Components\Section::make('الحالة والتقييم')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('cv_status')
                            ->options([
                                'تحتاج تأكيد' => 'تحتاج تأكيد',
                                'تمت الموافقة' => 'تمت الموافقة',
                                'قيد الانتظار' => 'قيد الانتظار',
                                'مرفوض' => 'مرفوض',
                            ])
                            ->required()
                            ->label('حالة السيرة الذاتية'),
                        Forms\Components\Placeholder::make('ai_score_display')
                            ->label('تقييم الذكاء الاصطناعي')
                            ->content(fn (?Cv $record) => $record && $record->ai_score !== null
                                ? "{$record->ai_score}/100"
                                : 'غير مقيّم بعد')
                            ->visibleOn('edit'),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->columnSpanFull()
                            ->nullable()
                            ->rows(4)
                            ->label('تعليقات / سبب الرفض'),
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
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->orderBy(
                            User::select('first_name')
                                ->whereColumn('users.id', 'cvs.user_id')
                                ->limit(1),
                            $direction
                        )
                    )
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('experience')
                    ->label('الخبرة')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('education')
                    ->label('التعليم')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('skills.name')
                    ->label('المهارات')
                    ->badge()
                    ->color('primary')
                    ->searchable(query: fn (Builder $query, string $search) =>
                        $query->whereHas('skills', fn (Builder $subQuery) =>
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
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
                        'تحتاج تأكيد' => 'warning',
                        'تمت الموافقة' => 'success',
                        'قيد الانتظار' => 'info',
                        'مرفوض' => 'danger',
                        default => 'secondary',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->label('التعليقات')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ الإنشاء'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cv_status')
                    ->options([
                        'تحتاج تأكيد' => 'تحتاج تأكيد',
                        'تمت الموافقة' => 'تمت الموافقة',
                        'قيد الانتظار' => 'قيد الانتظار',
                        'مرفوض' => 'مرفوض',
                    ])
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
                    ->modalDescription('سيتم إرسال بيانات هذه السيرة الذاتية إلى خدمة الذكاء الاصطناعي لتقييمها. هل تريد المتابعة؟')
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
                                ->body("حصلت السيرة الذاتية على درجة: {$result['score']}/100")
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('ai_analyze_batch')
                        ->label('تحليل جماعي بالذكاء الاصطناعي')
                        ->icon('heroicon-o-cpu-chip')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('تحليل جماعي بالذكاء الاصطناعي')
                        ->modalDescription('سيتم تحليل جميع السير الذاتية المحددة. قد تستغرق العملية بعض الوقت.')
                        ->action(function (Collection $records) {
                            $service = new AiCvScoringService();
                            $successCount = 0;
                            foreach ($records as $record) {
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
                                    $successCount++;
                                }
                            }
                            Notification::make()
                                ->title('اكتمل التحليل الجماعي')
                                ->body("تم تقييم {$successCount} من {$records->count()} سيرة ذاتية بنجاح.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
                                'تحتاج تأكيد' => 'warning',
                                'تمت الموافقة' => 'success',
                                'قيد الانتظار' => 'info',
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
                            ->label('تاريخ الإنشاء')
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
            'create' => Pages\CreateCv::route('/create'),
            'view' => Pages\ViewCv::route('/{record}'),
            'edit' => Pages\EditCv::route('/{record}/edit'),
        ];
    }
}