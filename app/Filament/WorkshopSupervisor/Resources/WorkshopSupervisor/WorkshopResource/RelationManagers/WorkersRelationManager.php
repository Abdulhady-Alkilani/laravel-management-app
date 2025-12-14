<?php

namespace App\Filament\WorkshopSupervisor\Resources\WorkshopResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class WorkersRelationManager extends RelationManager
{
    protected static string $relationship = 'workers';
    protected static ?string $title = 'العمال';
    protected static ?string $pluralTitle = 'العمال';
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
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    // <== هنا التعديل: إزالة searchable() و preloadRecordSelect() من AttachAction
                    // ->preloadRecordSelect() 
                    // ->searchable() 
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('اختر عاملاً')
                            ->helperText('اختر عاملاً لتعيينه لهذه الورشة.')
                            ->searchable() // <== تطبيق searchable() على حقل Select
                            ->preload() // <== تطبيق preload() على حقل Select
                            // تصفية العمال ليكونوا من لديهم أدوار عامل/مهندس ولم يتم إرفاقهم بالفعل بالورشة الحالية
                            ->getSearchResultsUsing(function (string $search): array {
                                $ownerWorkshopId = $this->getOwnerRecord()->id; // معرف الورشة الحالية
                                
                                $engineerRoles = [
                                    'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
                                    'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
                                    'Environmental Engineer', 'Surveying Engineer',
                                    'Information Technology Engineer',
                                    'Telecommunications Engineer',
                                ];

                                return User::query()
                                    ->whereHas('roles', fn (Builder $query) => 
                                        $query->where('name', 'Worker')
                                              ->orWhereIn('name', $engineerRoles)
                                    )
                                    // تصفية لاستبعاد العمال المرتبطين بالفعل بهذه الورشة
                                    ->whereDoesntHave('workerWorkshopLinks', fn(Builder $q) => 
                                        $q->where('workshop_id', $ownerWorkshopId)
                                    )
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
                            // <== تطبيق getOptionLabelFromRecordUsing على حقل Select
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