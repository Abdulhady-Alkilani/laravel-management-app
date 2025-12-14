<?php

namespace App\Filament\Resources\SkillResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Cv; // <== تأكد من استيراد Cv model
use App\Models\User; // <== تأكد من استيراد User model
use Illuminate\Database\Eloquent\Builder; // <== تأكد من استيراد Builder

class CvsRelationManager extends RelationManager
{
    protected static string $relationship = 'cvs'; // اسم العلاقة في Skill model
    protected static ?string $title = 'السير الذاتية المرتبطة'; // إضافة لغة عربية
    protected static ?string $pluralTitle = 'السير الذاتية المرتبطة'; // إضافة لغة عربية
    protected static ?string $modelLabel = 'سيرة ذاتية';       // إضافة لغة عربية
    protected static ?string $pluralModelLabel = 'سير ذاتية'; // إضافة لغة عربية

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // لا يوجد نموذج لإنشاء سيرة ذاتية هنا، بل لربط سير ذاتية موجودة أو عرضها
                // إذا أردت تعديل حقول Pivot (مثل تاريخ الربط)، يمكنك إضافتها هنا
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name') // يعرض اسم صاحب السيرة الذاتية (يفترض وجود name accessor في User model)
            ->columns([
                Tables\Columns\TextColumn::make('user.name') // <== استخدام user.name accessor
                    ->label('صاحب السيرة')
                    // <== استعلام بحث مخصص لـ user.name
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('user', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
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
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make() // لربط سير ذاتية موجودة بالمهارة
                    // <== لم نعد نضع preloadRecordSelect() و searchable() هنا مباشرة
                    // <== بدلاً من ذلك، سنضعها على حقل Select داخل الـ form() closure
                    ->form(function (Tables\Actions\AttachAction $action): array {
                        $currentSkillId = $this->getOwnerRecord()->id; // الحصول على معرف المهارة الحالية

                        return [
                            Forms\Components\Select::make('recordId')
                                ->label('اختر سيرة ذاتية')
                                ->helperText('اختر سيرة ذاتية لربطها بهذه المهارة.')
                                ->required()
                                ->preload() // <== يتم تطبيق preload هنا على Select
                                ->searchable() // <== يتم تطبيق searchable هنا على Select
                                // <== هنا يتم تعريف منطق البحث المخصص لجلب السير الذاتية المناسبة
                                ->getSearchResultsUsing(function (string $search) use ($currentSkillId) {
                                    return Cv::query()
                                        // استبعاد السير الذاتية المرتبطة بالفعل بهذه المهارة
                                        ->whereDoesntHave('skills', fn(Builder $q) => $q->where('skills.id', $currentSkillId))
                                        // البحث في تفاصيل صاحب السيرة أو حالة السيرة
                                        ->where(function (Builder $query) use ($search) {
                                            $query->whereHas('user', fn($userQuery) => $userQuery->where('first_name', 'like', "%{$search}%")
                                                                                                  ->orWhere('last_name', 'like', "%{$search}%")
                                                                                                  ->orWhere('email', 'like', "%{$search}%"))
                                                  ->orWhere('experience', 'like', "%{$search}%")
                                                  ->orWhere('education', 'like', "%{$search}%")
                                                  ->orWhere('cv_status', 'like', "%{$search}%");
                                        })
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(fn (Cv $cv) => [
                                            $cv->id => "{$cv->user->name} ({$cv->cv_status})" // عرض اسم المستخدم وحالة الـ CV
                                        ])
                                        ->toArray();
                                })
                                // <== هنا يتم تعريف كيفية عرض السجل المختار في الحقل
                                ->getOptionLabelFromRecordUsing(fn (Cv $record) => "{$record->user->name} ({$record->cv_status})"),
                        ];
                    }),
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