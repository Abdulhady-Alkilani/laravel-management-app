<?php

namespace App\Filament\Investor\Resources\Investor;

use App\Filament\Investor\Resources\Investor\ReportResource\Pages;
use App\Filament\Investor\Resources\Investor\ReportResource\RelationManagers;
use App\Models\Report;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Auth;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'التقارير';
    protected static ?string $pluralModelLabel = 'التقارير';
    protected static ?string $modelLabel = 'تقرير';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('project.investors', fn (Builder $query) => $query->where('investor_user_id', Auth::id()));
    }

    // <== تعطيل صلاحيات الإنشاء، التعديل، الحذف بشكل صارم
    protected static bool $canCreate = false;
    protected static bool $canEdit = false;
    protected static bool $canDelete = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('employee_name')
                    ->content(fn (Report $record) => $record->employee->name ?? 'N/A')
                    ->label('الموظف مقدم التقرير'),
                Forms\Components\Placeholder::make('project_name')
                    ->content(fn (Report $record) => $record->project->name ?? 'N/A')
                    ->label('المشروع'),
                Forms\Components\Placeholder::make('workshop_name')
                    ->content(fn (Report $record) => $record->workshop->name ?? 'N/A')
                    ->label('الورشة'),
                Forms\Components\Placeholder::make('service_name')
                    ->content(fn (Report $record) => $record->service->name ?? 'N/A')
                    ->label('الخدمة'),
                Forms\Components\TextInput::make('report_type')
                    ->label('نوع التقرير')
                    ->disabled(),
                RichEditor::make('report_details')
                    ->columnSpanFull()
                    ->label('تفاصيل التقرير')
                    ->disabled(),
                Forms\Components\TextInput::make('report_status')
                    ->label('حالة التقرير')
                    ->disabled(),
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('تاريخ الإنشاء'),
                Tables\Columns\TextColumn::make('report_status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'view' => Pages\ViewReport::route('/{record}'),
        ];
    }
}