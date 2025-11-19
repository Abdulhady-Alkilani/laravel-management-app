<?php

namespace App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor;

use App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\ReportResource\Pages;
use App\Models\Report;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Workshop;
use Closure;
use Illuminate\Support\Collection;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'تقاريري';
    protected static ?string $pluralModelLabel = 'تقاريري';
    protected static ?string $modelLabel = 'تقرير ورشة';

    public static function getEloquentQuery(): Builder
    {
        $supervisorId = Auth::id();
        return parent::getEloquentQuery()
            ->whereHas('workshop', fn (Builder $query) => $query->where('supervisor_user_id', $supervisorId));
    }

    protected static bool $canCreate = true;
    protected static bool $canEdit = true;
    protected static bool $canDelete = true;

    public static function form(Form $form): Form
    {
        $supervisorId = Auth::id();

        return $form
            ->schema([
                Forms\Components\Select::make('workshop_id')
                    ->relationship('workshop', 'name', fn (Builder $query) => $query->where('supervisor_user_id', $supervisorId))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->label('الورشة')
                    ->helperText('الورشة التي يتعلق بها التقرير.')
                    ->disabledOn('edit'),
                
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'first_name', function (Builder $query, Forms\Get $get) use ($supervisorId) {
                        $selectedWorkshopId = $get('workshop_id');
                        
                        $query->select('users.id', 'users.first_name', 'users.last_name', 'users.email');

                        if (!$selectedWorkshopId) {
                            return $query->where('users.id', Auth::id());
                        }
                        
                        return $query->where('users.id', Auth::id())
                            ->orWhereHas('workerWorkshopLinks', fn ($subQuery) => $subQuery->where('workshop_id', $selectedWorkshopId));
                    })
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('الموظف مقدم التقرير')
                    ->helperText('اختر الموظف (يمكن أن تكون أنت) الذي يقدم هذا التقرير.')
                    ->disabledOn('edit'),
                
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name', function (Builder $query, Forms\Get $get) use ($supervisorId) {
                        $selectedWorkshopId = $get('workshop_id');
                        if (!$selectedWorkshopId) {
                            return $query->whereNull('id'); // لا يوجد مشروع إذا لم يتم اختيار ورشة
                        }
                        // تصفية المشاريع التي تحتوي على الورشة المختارة
                        return $query->whereHas('workshops', fn ($subQuery) => $subQuery->where('id', $selectedWorkshopId));
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('المشروع')
                    ->helperText('المشروع الذي يتعلق به التقرير (اختياري).')
                    ->disabledOn('edit'),
                
                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('الخدمة')
                    ->helperText('الخدمة التي يتعلق بها التقرير (اختياري).')
                    ->disabledOn('edit'),
                
                Forms\Components\TextInput::make('report_type')
                    ->required()
                    ->maxLength(255)
                    ->label('نوع التقرير')
                    ->helperText('مثال: تقرير تقدم، تقرير إنتاجية، تقرير جودة.')
                    ->disabled(fn (string $operation, ?Report $record) => $operation === 'edit' && $record?->employee_id !== Auth::id()),
                
                RichEditor::make('report_details')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('تفاصيل التقرير')
                    ->helperText('وصف مفصل لمحتوى التقرير.')
                    ->disabled(fn (string $operation, ?Report $record) => $operation === 'edit' && $record?->employee_id !== Auth::id()),
                
                Forms\Components\Select::make('report_status')
                    ->options([
                        'معلقة' => 'معلقة',
                        'تمت المراجعة' => 'تمت المراجعة',
                        'مرفوض' => 'مرفوض',
                        'تمت الموافقة' => 'تمت الموافقة',
                    ])
                    ->required()
                    ->default('معلقة')
                    ->label('حالة التقرير')
                    ->helperText('الحالة الحالية للتقرير.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('الموظف')
                    // <== التعديل الرئيسي هنا: استخدام استعلام مخصص للبحث
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('employee', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('المشروع')
                    // <== التعديل الرئيسي هنا: استخدام استعلام مخصص للبحث
                    ->searchable(query: fn (Builder $query, string $search) =>
                        $query->whereHas('project', fn (Builder $subQuery) =>
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('workshop.name')
                    ->label('الورشة')
                    // <== التعديل الرئيسي هنا: استخدام استعلام مخصص للبحث
                    ->searchable(query: fn (Builder $query, string $search) =>
                        $query->whereHas('workshop', fn (Builder $subQuery) =>
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_type')
                    ->searchable()
                    ->sortable()
                    ->label('نوع التقرير'),
                Tables\Columns\TextColumn::make('report_status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'معلقة' => 'warning', 'تمت المراجعة' => 'info',
                        'تمت الموافقة' => 'success', 'مرفوض' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Report $record) => $record->employee_id === Auth::id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(fn (?Collection $records) => $records && $records->every(fn (Report $record) => $record->employee_id === Auth::id())),
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
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}