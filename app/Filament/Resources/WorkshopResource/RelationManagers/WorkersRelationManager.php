<?php

namespace App\Filament\Resources\WorkshopResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User; // <== تأكد من استيراد User model
use Illuminate\Database\Eloquent\Builder; // <== تأكد من استيراد Builder

class WorkersRelationManager extends RelationManager
{
    protected static string $relationship = 'workers'; // اسم العلاقة في Workshop model
    protected static ?string $title = 'العمال المرتبطون'; // إضافة لغة عربية
    protected static ?string $pluralTitle = 'العمال المرتبطون'; // إضافة لغة عربية
    protected static ?string $modelLabel = 'عامل'; // إضافة لغة عربية
    protected static ?string $pluralModelLabel = 'عمال'; // إضافة لغة عربية

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('assigned_date')
                    ->nullable()
                    ->label('تاريخ التعيين'),
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
                Tables\Columns\TextColumn::make('pivot.assigned_date')->label('تاريخ التعيين')->date()->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    // <== لم نعد نضع preloadRecordSelect() و searchable() هنا مباشرة
                    // <== بدلاً من ذلك، سنضعها على حقل Select داخل الـ form() closure
                    ->form(function (Tables\Actions\AttachAction $action): array {
                        $currentWorkshopId = $this->getOwnerRecord()->id; // الحصول على معرف الورشة الحالية

                        return [
                            Forms\Components\Select::make('recordId')
                                ->label('اختر عاملاً')
                                ->helperText('اختر عاملاً لربطه بهذه الورشة.')
                                ->required()
                                ->preload() // <== يتم تطبيق preload هنا على Select
                                ->searchable() // <== يتم تطبيق searchable هنا على Select
                                // <== هنا يتم تعريف منطق البحث المخصص لجلب المستخدمين المناسبين
                                ->getSearchResultsUsing(function (string $search) use ($currentWorkshopId) {
                                    return User::query()
                                        // تصفية المستخدمين ليكونوا ذوي دور 'Worker' (أو أدوار المهندسين)
                                        ->whereHas('roles', fn (Builder $roleQuery) => 
                                            $roleQuery->where('name', 'Worker')
                                                      ->orWhere('name', 'Architectural Engineer')
                                                      ->orWhere('name', 'Civil Engineer')
                                                      ->orWhere('name', 'Structural Engineer')
                                                      ->orWhere('name', 'Electrical Engineer')
                                                      ->orWhere('name', 'Mechanical Engineer')
                                                      ->orWhere('name', 'Geotechnical Engineer')
                                                      ->orWhere('name', 'Quantity Surveyor')
                                                      ->orWhere('name', 'Site Engineer')
                                                      ->orWhere('name', 'Environmental Engineer')
                                                      ->orWhere('name', 'Surveying Engineer')
                                        )
                                        // استبعاد المستخدمين المرتبطين بالفعل بهذه الورشة
                                        ->whereDoesntHave('workerWorkshopLinks', fn(Builder $q) => $q->where('workshop_id', $currentWorkshopId))
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
                            Forms\Components\DatePicker::make('assigned_date')
                                ->nullable()
                                ->label('تاريخ التعيين'),
                        ];
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}