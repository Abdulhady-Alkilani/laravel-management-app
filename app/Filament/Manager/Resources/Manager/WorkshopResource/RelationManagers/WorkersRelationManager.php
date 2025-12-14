<?php

namespace App\Filament\Manager\Resources\Manager\ProjectResource\RelationManagers;
// خطأ في النسخ/اللصق هنا، يجب أن يكون
// namespace App\Filament\Manager\Resources\WorkshopResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class WorkersRelationManager extends RelationManager
{
    protected static string $relationship = 'workers'; // اسم العلاقة في Workshop model
    protected static ?string $title = 'العمال المرتبطون';
    protected static ?string $pluralTitle = 'العمال المرتبطون';
    protected static ?string $modelLabel = 'عامل';
    protected static ?string $pluralModelLabel = 'عمال';


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
            ->recordTitleAttribute('name') // 'name' accessor في User model
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->label('الاسم الأول')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')->label('الاسم الأخير')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('البريد الإلكتروني')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('pivot.assigned_date')
                    ->label('تاريخ التعيين')
                    ->date(),
                    // ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    // <== هنا التعديل: نقل getSearchResultsUsing و getOptionLabelFromRecordUsing إلى حقل Select
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        Forms\Components\Select::make('recordId') // <== هذا هو الـ Select Component
                            ->label('اختر عاملاً')
                            ->helperText('اختر عاملاً لربطه بهذه الورشة.')
                            ->searchable() // <== تمكين البحث هنا على الـ Select Component
                            // تصفية العمال ليكونوا من لديهم أدوار عامل/مهندس فقط
                            ->getSearchResultsUsing(fn (string $search) => User::whereHas('roles', fn ($query) => $query->whereIn('name', ['Worker', 'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer', 'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer', 'Environmental Engineer', 'Surveying Engineer']))
                                ->where(fn ($query) => $query->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%"))
                                ->limit(50)
                                ->get())
                            ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->first_name} {$record->last_name} ({$record->email})"),
                        Forms\Components\DatePicker::make('assigned_date')
                            ->nullable()
                            ->label('تاريخ التعيين')
                            ->helperText('تاريخ تعيين العامل لهذه الورشة.'),
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