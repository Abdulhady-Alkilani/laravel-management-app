<?php

namespace App\Filament\Manager\Resources\Manager\ProjectResource\RelationManagers;

use App\Models\Report;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Workshop;
use App\Models\Service;

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';
    protected static ?string $title = 'التقارير';
    protected static ?string $pluralTitle = 'التقارير';
    protected static ?string $modelLabel = 'تقرير';
    protected static ?string $pluralModelLabel = 'تقارير';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('employee_id')
                    ->default(auth()->id())
                    ->dehydrated(true),
                Forms\Components\Placeholder::make('employee_info')
                    ->content(fn (?Report $record, string $operation) => $operation === 'create' ? auth()->user()->name : ($record?->employee->name ?? 'غير معروف'))
                    ->label('الموظف مقدم التقرير')
                    ->visible(fn (string $operation): bool => in_array($operation, ['create', 'edit', 'view'])),

                Forms\Components\Hidden::make('project_id')
                    ->default($this->getOwnerRecord()->id)
                    ->dehydrated(true),
                Forms\Components\Placeholder::make('project_info')
                    ->content(fn () => $this->getOwnerRecord()->name)
                    ->label('المشروع التابع له التقرير')
                    ->visible(fn (string $operation): bool => in_array($operation, ['create', 'edit', 'view'])),

                Forms\Components\Placeholder::make('workshop_info')
                    ->content(fn (?Report $record) => $record?->workshop->name ?? 'لا يوجد ورشة')
                    ->label('الورشة')
                    ->visible(fn (string $operation): bool => in_array($operation, ['edit', 'view'])),

                Forms\Components\Placeholder::make('service_info')
                    ->content(fn (?Report $record) => $record?->service->name ?? 'لا يوجد خدمة')
                    ->label('الخدمة')
                    ->visible(fn (string $operation): bool => in_array($operation, ['edit', 'view'])),

                Forms\Components\TextInput::make('report_type')
                    ->required()
                    ->maxLength(255)
                    ->label('نوع التقرير')
                    ->disabled(fn (string $operation): bool => in_array($operation, ['edit', 'view'])),
                
                RichEditor::make('report_details')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('تفاصيل التقرير')
                    ->disabled(fn (string $operation): bool => in_array($operation, ['edit', 'view'])),

                Forms\Components\Select::make('report_status')
                    ->options([
                        'معلقة' => 'معلقة',
                        'تمت المراجعة' => 'تمت المراجعة',
                        'مرفوض' => 'مرفوض',
                        'تمت الموافقة' => 'تمت الموافقة',
                    ])
                    ->required()
                    ->default('تمت المراجعة') // <== لا يزال كقيمة افتراضية، لكن المستخدم يمكنه تغييرها
                    ->disabled(fn (string $operation): bool => $operation === 'view') // <== التعديل هنا: معطل فقط عند العرض
                    ->label('حالة التقرير'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('report_type')
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('الموظف')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('workshop.name')
                    ->label('الورشة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('الخدمة')
                    ->searchable()
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
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}