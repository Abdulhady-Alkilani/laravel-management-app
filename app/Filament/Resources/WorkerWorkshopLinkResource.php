<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkerWorkshopLinkResource\Pages;
use App\Models\WorkerWorkshopLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User; // <== تأكد من استيراد User model
use App\Models\Workshop; // <== تأكد من استيراد Workshop model
use Illuminate\Database\Eloquent\Builder; // <== تأكد من استيراد Builder

class WorkerWorkshopLinkResource extends Resource
{
    protected static ?string $model = WorkerWorkshopLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationLabel = 'ربط العمال بالورشات';
    protected static ?string $pluralModelLabel = 'روابط العمال بالورشات';
    protected static ?string $modelLabel = 'رابط عامل بورشة';

    public static function form(Form $form): Form
    {
        // تعريف أدوار العمال والمهندسين
        $engineerAndWorkerRolesNames = [
            'Worker',
            'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
            'Environmental Engineer', 'Surveying Engineer', 'Information Technology Engineer', 'Telecommunications Engineer',
        ];

        return $form
            ->schema([
                Forms\Components\Select::make('worker_id')
                    // <== التعديل الرئيسي هنا: تصفية المستخدمين ليعرض العمال والمهندسين فقط
                    ->relationship('worker', 'first_name', fn (Builder $query) => 
                        $query->whereHas('roles', fn (Builder $roleQuery) => 
                            $roleQuery->whereIn('name', $engineerAndWorkerRolesNames)
                        )
                        // يمكنك إضافة شرط إضافي هنا لمنع ربط نفس العامل بأكثر من ورشة إذا كان ذلك هو منطق عملك
                        // ->whereDoesntHave('workerWorkshopLinks') 
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('العامل'),
                Forms\Components\Select::make('workshop_id')
                    ->relationship('workshop', 'name') // الأدمن يمكنه رؤية جميع الورش
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
                Tables\Columns\TextColumn::make('worker.name') // <== استخدام worker.name accessor
                    ->label('العامل')
                    // <== استعلام بحث مخصص لـ worker.name
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
                    // <== استعلام بحث مخصص لـ workshop.name
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ الإنشاء'),
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