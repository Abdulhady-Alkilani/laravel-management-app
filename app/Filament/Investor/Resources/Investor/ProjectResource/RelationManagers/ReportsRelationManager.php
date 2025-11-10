<?php

namespace App\Filament\Investor\Resources\Investor\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use App\Models\Report; // استيراد Report model

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';
    protected static ?string $title = 'التقارير';
    protected static ?string $pluralTitle = 'التقارير';
    protected static ?string $modelLabel = 'تقرير';
    protected static ?string $pluralModelLabel = 'تقارير';

    // <== تعطيل صلاحيات الإنشاء، التعديل، الحذف داخل العلاقة أيضاً
    protected static bool $canCreate = false;
    protected static bool $canEdit = false;
    protected static bool $canDelete = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('employee_name')
                    ->content(fn (Report $record) => $record->employee->name ?? 'N/A')
                    ->label('الموظف مقدم التقرير'),
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('report_type')
            ->columns([
                Tables\Columns\TextColumn::make('report_type')
                    ->searchable()
                    ->sortable()
                    ->label('نوع التقرير'),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('الموظف')
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
}