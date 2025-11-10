<?php

namespace App\Filament\Manager\Resources\Manager\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User; // لاستيراد User model
// لا تحتاج لاستيراد Role model مباشرة هنا

class InvestorsRelationManager extends RelationManager
{
    protected static string $relationship = 'investors';
    protected static ?string $title = 'المستثمرون';
    protected static ?string $pluralTitle = 'المستثمرون';
    protected static ?string $modelLabel = 'مستثمر';
    protected static ?string $pluralModelLabel = 'مستثمرون';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('investment_amount')
                    ->numeric()
                    ->required()
                    ->prefix('SR')
                    ->label('مبلغ الاستثمار')
                    ->helperText('أدخل المبلغ الذي استثمره هذا المستخدم في هذا المشروع.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name') // 'name' accessor في User model
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->label('الاسم الأول')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')->label('الاسم الأخير')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('البريد الإلكتروني')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('pivot.investment_amount')
                    ->label('مبلغ الاستثمار')
                    ->money('SAR')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    // لم نعد بحاجة إلى preloadRecordSelect() إذا كنا نستخدم getSearchResultsUsing لتعبئة الخيارات
                    // ->preloadRecordSelect() 
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        Forms\Components\Select::make('recordId') // <== اسم الحقل هنا هو 'recordId'
                            ->label('اختر مستثمراً')
                            ->helperText('اختر مستمرًا لربطه بالمشروع.')
                            ->required()
                            ->searchable() // <== تمكين البحث في قائمة الاختيار
                            // <== التعديل الرئيسي هنا: استخدام getSearchResultsUsing لجلب المستخدمين كـ "مستثمر"
                            ->getSearchResultsUsing(function (string $search): array {
                                return User::query()
                                    ->whereHas('roles', fn (Builder $subQuery) => $subQuery->where('name', 'Investor'))
                                    // التأكد من أن المستخدم ليس مرتبطًا بالفعل بهذا المشروع
                                    ->whereDoesntHave('projectInvestorLinks', fn(Builder $q) => $q->where('project_id', $this->getOwnerRecord()->id))
                                    ->where(function (Builder $query) use ($search) {
                                        $query->where('first_name', 'like', "%{$search}%")
                                            ->orWhere('last_name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%");
                                    })
                                    ->limit(50) // حد أقصى للنتائج المعروضة
                                    ->get()
                                    ->mapWithKeys(fn (User $user) => [
                                        $user->id => "{$user->first_name} {$user->last_name} ({$user->email})"
                                    ])
                                    ->toArray();
                            })
                            // <== استخدام getOptionLabelFromRecordUsing لضمان عرض الاسم الصحيح بعد الاختيار
                            ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->first_name} {$record->last_name} ({$record->email})"),
                        Forms\Components\TextInput::make('investment_amount')
                            ->numeric()
                            ->required()
                            ->prefix('SR')
                            ->label('مبلغ الاستثمار'),
                    ]),
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