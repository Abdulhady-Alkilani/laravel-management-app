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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use App\Models\Role;
use App\Services\AiCvScoringService;
use Filament\Notifications\Notification;

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
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'first_name', fn (Builder $query) =>
                        $query->whereHas('roles', fn (Builder $roleQuery) =>
                            $roleQuery->whereIn('name', $engineerAndWorkerRolesNames)
                        )
                        // يمكنك إضافة شرط إضافي هنا لضمان أن المستخدم ليس لديه CV بالفعل، إذا كنت لا تريد أكثر من CV لكل مستخدم
                        ->whereDoesntHave('cvs') 
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('المستخدم')
                    ->disabledOn('edit'), // تعطيل الحقل عند التعديل
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('صاحب السيرة')
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
                Tables\Columns\IconColumn::make('cv_file_path')
                    ->label('ملف CV')
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-arrow-down' : 'heroicon-o-x-mark')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->tooltip(fn ($state) => $state ? 'انقر لعرض الملف' : 'لا يوجد ملف')
                    ->action(fn (Cv $record) => $record->cv_file_path ? response()->download(storage_path('app/public/' . $record->cv_file_path)) : null),
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
                    ->label('سبب الرفض')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('skills.name')
                    ->label('المهارات')
                    ->badge()
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
                Tables\Filters\SelectFilter::make('cv_status')
                    ->options([
                        'تحتاج تأكيد' => 'تحتاج تأكيد',
                        'تمت الموافقة' => 'تمت الموافقة',
                        'قيد الانتظار' => 'قيد الانتظار',
                        'مرفوض' => 'مرفوض',
                    ])
                    ->default('قيد الانتظار')
                    ->label('حالة السيرة الذاتية'),
            ])
            ->actions([
                Action::make('ai_analyze')
                    ->label('تحليل بالذكاء الاصطناعي')
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
                        $score = $service->scoreCv($cvData);
                        if ($score !== null) {
                            $record->update(['ai_score' => $score]);
                            Notification::make()
                                ->title('تم التقييم بنجاح')
                                ->body("حصلت السيرة الذاتية على درجة: {$score}/100")
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
                                $score = $service->scoreCv($cvData);
                                if ($score !== null) {
                                    $record->update(['ai_score' => $score]);
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
            ->defaultSort('ai_score', 'desc');
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