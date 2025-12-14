<?php

namespace App\Filament\Resources\RoleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User; // <== تأكد من استيراد User model
use Illuminate\Database\Eloquent\Builder; // <== تأكد من استيراد Builder

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users'; // اسم العلاقة في Role model
    protected static ?string $title = 'المستخدمون';      // إضافة لغة عربية
    protected static ?string $pluralTitle = 'المستخدمون'; // إضافة لغة عربية
    protected static ?string $modelLabel = 'مستخدم';     // إضافة لغة عربية
    protected static ?string $pluralModelLabel = 'مستخدمون'; // إضافة لغة عربية

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
            ->recordTitleAttribute('name') // اسم المستخدم الكامل (يفترض وجود name accessor في User model)
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
                    // <== لم نعد نضع preloadRecordSelect() و searchable() هنا مباشرة
                    // بدلاً من ذلك، سنضعها على حقل Select داخل الـ form() closure
                    ->form(function (Tables\Actions\AttachAction $action): array {
                        $currentRoleId = $this->getOwnerRecord()->id; // الحصول على معرف الدور الحالي

                        return [
                            Forms\Components\Select::make('recordId')
                                ->label('اختر مستخدماً')
                                ->helperText('اختر مستخدماً لربطه بهذا الدور.')
                                ->required()
                                ->preload() // <== يتم تطبيق preload هنا على Select
                                ->searchable() // <== يتم تطبيق searchable هنا على Select
                                // <== هنا يتم تعريف منطق البحث المخصص لجلب المستخدمين المناسبين
                                ->getSearchResultsUsing(function (string $search) use ($currentRoleId) {
                                    return User::query()
                                        // استبعاد المستخدمين المرتبطين بالفعل بهذا الدور
                                        ->whereDoesntHave('roles', fn(Builder $q) => $q->where('roles.id', $currentRoleId))
                                        // شروط البحث (بالاسم الأول، الأخير، أو البريد الإلكتروني)
                                        ->where(function (Builder $query) use ($search) {
                                            $query->where('first_name', 'like', "%{$search}%")
                                                ->orWhere('last_name', 'like', "%{$search}%")
                                                ->orWhere('email', 'like', "%{$search}%");
                                        })
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(fn (User $user) => [
                                            $user->id => "{$user->first_name} {$user->last_name} ({$user->email})"
                                        ])
                                        ->toArray();
                                })
                                // <== هنا يتم تعريف كيفية عرض السجل المختار في الحقل
                                ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->first_name} {$record->last_name} ({$record->email})"),
                        ];
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(), // لفصل المستخدم عن الدور
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}