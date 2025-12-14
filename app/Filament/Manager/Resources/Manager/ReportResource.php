<?php

namespace App\Filament\Manager\Resources\Manager;

use App\Filament\Manager\Resources\Manager\ReportResource\Pages;
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
use App\Models\Project;
use App\Models\Workshop;
use App\Models\Service;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'التقارير';
    protected static ?string $pluralModelLabel = 'التقارير';
    protected static ?string $modelLabel = 'تقرير';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('project', function (Builder $query) {
            $query->where('manager_user_id', Auth::id());
        });
    }

    public static function form(Form $form): Form
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

                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name', fn (Builder $query) => $query->where('manager_user_id', Auth::id()))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('المشروع')
                    ->visible(fn (string $operation): bool => $operation === 'create'),
                Forms\Components\Placeholder::make('project_info_display')
                    ->content(fn (?Report $record) => $record?->project->name ?? 'لا يوجد مشروع')
                    ->label('المشروع التابع له التقرير')
                    ->visible(fn (string $operation): bool => in_array($operation, ['edit', 'view'])),

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('الموظف')
                    ->searchable(),
                    // ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('المشروع')
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