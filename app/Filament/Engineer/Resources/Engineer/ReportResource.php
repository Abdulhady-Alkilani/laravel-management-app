<?php

namespace App\Filament\Engineer\Resources\Engineer;

use App\Filament\Engineer\Resources\Engineer\ReportResource\Pages;
use App\Models\Report;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\Workshop;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'تقاريري';
    protected static ?string $pluralModelLabel = 'تقاريري';
    protected static ?string $modelLabel = 'تقرير';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('employee_id', Auth::id());
    }

    protected static bool $canCreate = true;
    protected static bool $canEdit = true;
    protected static bool $canDelete = true;

    public static function form(Form $form): Form
    {
        $engineerId = Auth::id();

        return $form
            ->schema([
                Forms\Components\Hidden::make('employee_id')
                    ->default($engineerId)
                    ->dehydrated(true),
                Forms\Components\Placeholder::make('employee_info')
                    ->content(fn () => Auth::user()->name)
                    ->label('الموظف مقدم التقرير'),

                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name', fn (Builder $query, Forms\Get $get) => // <== إضافة Forms\Get $get
                        $query->whereHas('tasks', fn ($subQuery) => $subQuery->where('assigned_to_user_id', $engineerId))
                              // <== التعديل الرئيسي هنا: تصفية المشاريع بناءً على الورشة المختارة (إذا وجدت)
                              ->when($get('workshop_id'), fn (Builder $query, $workshopId) => 
                                  $query->whereHas('workshops', fn ($q) => $q->where('id', $workshopId))
                              )
                    )
                    ->getOptionLabelFromRecordUsing(fn (Project $record) => $record->name)
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->live() // <== مهم جداً لتشغيل التحديث الديناميكي
                    // <== تم حذف ->afterStateUpdated(fn (Forms\Set $set) => $set('workshop_id', null))
                    ->label('المشروع'),

                Forms\Components\Select::make('workshop_id')
                    ->relationship('workshop', 'name', fn (Builder $query, Forms\Get $get) => // <== إضافة Forms\Get $get
                        $query->whereHas('tasks', fn ($subQuery) => $subQuery->where('assigned_to_user_id', $engineerId))
                              // <== التعديل الرئيسي هنا: تصفية الورش بناءً على المشروع المختار (إذا وجد)
                              ->when($get('project_id'), fn (Builder $query, $projectId) => 
                                  $query->where('project_id', $projectId)
                              )
                    )
                    ->getOptionLabelFromRecordUsing(fn (Workshop $record) => $record->name)
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->live() // <== مهم جداً لتشغيل التحديث الديناميكي
                    // <== تم حذف ->afterStateUpdated(fn (Forms\Set $set) => $set('project_id', null))
                    ->label('الورشة'),

                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('الخدمة'),

                Forms\Components\TextInput::make('report_type')
                    ->required()
                    ->maxLength(255)
                    ->label('نوع التقرير'),
                RichEditor::make('report_details')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('تفاصيل التقرير'),
                
                Forms\Components\Hidden::make('report_status')
                    ->default('معلقة')
                    ->dehydrated(true),
                Forms\Components\Placeholder::make('current_report_status')
                    ->content(fn (?Report $record) => $record?->report_status ?? 'معلقة')
                    ->label('حالة التقرير')
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_type')
                    ->searchable()
                    ->sortable()
                    ->label('نوع التقرير'),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('المشروع')
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('project', fn (Builder $subQuery) => 
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('workshop.name')
                    ->label('الورشة')
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('workshop', fn (Builder $subQuery) => 
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('تاريخ الإنشاء'),
                Tables\Columns\TextColumn::make('report_status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'معلقة' => 'warning', 'تمت المراجعة' => 'info',
                        'تمت الموافقة' => 'success', 'مرفوض' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}