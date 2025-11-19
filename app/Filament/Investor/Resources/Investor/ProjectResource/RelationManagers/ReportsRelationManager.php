<?php

namespace App\Filament\Investor\Resources\Investor\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use App\Models\Report;
use App\Models\User; // تأكد من استيراد User model

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';
    protected static ?string $title = 'التقارير';
    protected static ?string $pluralTitle = 'التقارير';
    protected static ?string $modelLabel = 'تقرير';
    protected static ?string $pluralModelLabel = 'تقارير';

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
                    // <== التعديل الرئيسي هنا: استخدام استعلام مخصص للبحث
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('employee', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
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
            ])
            ->bulkActions([
                //
            ]);
    }
}