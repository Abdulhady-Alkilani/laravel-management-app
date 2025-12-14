<?php

namespace App\Filament\Investor\Resources\Investor;

use App\Filament\Investor\Resources\Investor\ProjectResource\Pages;
use App\Filament\Investor\Resources\Investor\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Auth;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'مشاريعي';
    protected static ?string $pluralModelLabel = 'مشاريعي';
    protected static ?string $modelLabel = 'مشروع';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('investors', fn (Builder $query) => $query->where('investor_user_id', Auth::id()));
    }

    // <== تعطيل صلاحيات الإنشاء، التعديل، الحذف بشكل صارم
    protected static bool $canCreate = false;
    protected static bool $canEdit = false;
    protected static bool $canDelete = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم المشروع')
                    ->disabled(),
                RichEditor::make('description')
                    ->columnSpanFull()
                    ->label('وصف المشروع')
                    ->disabled(),
                Forms\Components\TextInput::make('location')
                    ->label('الموقع الجغرافي')
                    ->disabled(),
                Forms\Components\TextInput::make('budget')
                    ->numeric()
                    ->prefix('SR')
                    ->label('الميزانية المخصصة')
                    ->disabled(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('تاريخ البدء المخطط')
                    ->disabled(),
                Forms\Components\DatePicker::make('end_date_planned')
                    ->label('تاريخ الانتهاء المخطط')
                    ->disabled(),
                Forms\Components\DatePicker::make('end_date_actual')
                    ->label('تاريخ الانتهاء الفعلي')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'مخطط' => 'مخطط', 'قيد التنفيذ' => 'قيد التنفيذ',
                        'مكتمل' => 'مكتمل', 'متوقف' => 'متوقف',
                    ])
                    ->label('حالة المشروع')
                    ->disabled(),
                Forms\Components\Placeholder::make('manager_info')
                    ->content(fn (Project $record) => $record->manager->name ?? 'غير محدد')
                    ->label('مدير المشروع'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('اسم المشروع'),
                Tables\Columns\TextColumn::make('pivot.investment_amount')
                    ->label('مبلغ استثماري')
                    ->money('SYP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('budget')
                    ->money('SYP')
                    ->sortable()
                    ->label('إجمالي الميزانية'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'مخطط' => 'info', 'قيد التنفيذ' => 'primary',
                        'مكتمل' => 'success', 'متوقف' => 'danger',
                        default => 'secondary',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable()
                    ->label('تاريخ البدء'),
                Tables\Columns\TextColumn::make('end_date_planned')
                    ->date()
                    ->sortable()
                    ->label('تاريخ الانتهاء المخطط'),
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->getStateUsing(fn (Project $record) => $record->tasks()->avg('progress') ?? 0)
                    ->label('متوسط التقدم (%)')
                    ->formatStateUsing(fn ($state) => number_format($state, 0) . '%'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // <== فقط زر العرض
            ])
            ->bulkActions([
                // <== لا توجد إجراءات مجمعة
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ReportsRelationManager::class, // <== ستكون للقراءة فقط
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'view' => Pages\ViewProject::route('/{record}'),
        ];
    }
}