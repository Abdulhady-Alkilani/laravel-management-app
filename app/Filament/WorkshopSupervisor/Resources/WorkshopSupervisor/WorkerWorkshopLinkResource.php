<?php

namespace App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor;

use App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\WorkerWorkshopLinkResource\Pages;
use App\Models\WorkerWorkshopLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Validation\Rules\Unique; // استيراد Unique من Laravel

class WorkerWorkshopLinkResource extends Resource
{
    protected static ?string $model = WorkerWorkshopLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'عمال الورشات';
    protected static ?string $pluralModelLabel = 'عمال الورشات';
    protected static ?string $modelLabel = 'عامل ورشة';

    public static function getEloquentQuery(): Builder
    {
        $supervisorId = Auth::id();
        return parent::getEloquentQuery()
            ->whereHas('workshop', fn (Builder $query) => $query->where('supervisor_user_id', $supervisorId));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('worker_id')
                    ->label('العامل')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->getSearchResultsUsing(function (string $search): array {
                        $engineerRoles = [
                            'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
                            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
                            'Environmental Engineer', 'Surveying Engineer',
                            'Information Technology Engineer',
                            'Telecommunications Engineer',
                        ];
                        
                        return User::query()
                            ->whereHas('roles', fn (Builder $roleQuery) => 
                                $roleQuery->where('name', 'Worker')
                                          ->orWhereIn('name', $engineerRoles)
                            )
                            ->whereHas('cvs', fn (Builder $cvQuery) => $cvQuery->where('cv_status', 'تمت الموافقة'))
                            ->whereDoesntHave('workerWorkshopLinks')
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
                    ->getOptionLabelUsing(fn (?int $value): ?string => 
                        $value ? (User::find($value)?->name ?? 'غير معروف') : null
                    )
                    ->unique(
                        ignoreRecord: true, 
                        modifyRuleUsing: fn (Unique $rule, Forms\Get $get) => $rule->where('workshop_id', $get('workshop_id'))
                    )
                    ->helperText('اختر عاملاً أو مهندساً (بصلاحية CV تمت الموافقة، وغير مرتبط بورشة حالياً) لتعيينه للورشة.'),

                Forms\Components\Select::make('workshop_id')
                    ->relationship('workshop', 'name', fn (Builder $query) => $query->where('supervisor_user_id', Auth::id()))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('الورشة'),
                    
                Forms\Components\DatePicker::make('assigned_date')
                    ->nullable()
                    ->label('تاريخ التعيين'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('worker.name')
                    ->label('العامل')
                    // <== التعديل الرئيسي هنا: استخدام استعلام مخصص للبحث
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('worker', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
            ),
                    // ->sortable(),
                Tables\Columns\TextColumn::make('workshop.name')
                    ->label('الورشة')
                    // <== التعديل الرئيسي هنا: استخدام استعلام مخصص للبحث
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('workshop', fn (Builder $subQuery) => 
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('assigned_date')
                    ->date()
                    ->sortable()
                    ->label('تاريخ التعيين'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkerWorkshopLinks::route('/'),
            'create' => Pages\CreateWorkerWorkshopLink::route('/create'),
            'edit' => Pages\EditWorkerWorkshopLink::route('/{record}/edit'),
        ];
    }
}