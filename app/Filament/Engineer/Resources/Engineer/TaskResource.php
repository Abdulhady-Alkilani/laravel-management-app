<?php

namespace App\Filament\Engineer\Resources\Engineer;

use App\Filament\Engineer\Resources\Engineer\TaskResource\Pages;
use App\Filament\Engineer\Resources\Engineer\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Auth;
use Closure; // <== استيراد Closure

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'مهامي الفنية';
    protected static ?string $pluralModelLabel = 'مهامي الفنية';
    protected static ?string $modelLabel = 'مهمة';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('assigned_to_user_id', Auth::id());
    }

    protected static bool $canCreate = false;
    protected static bool $canEdit = true;
    protected static bool $canDelete = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('project_info')
                    ->content(fn (Task $record) => $record->project->name ?? 'N/A')
                    ->label('المشروع'),
                Forms\Components\Placeholder::make('workshop_info')
                    ->content(fn (Task $record) => $record->workshop->name ?? 'N/A')
                    ->label('الورشة'),
                RichEditor::make('description')
                    ->required()
                    ->columnSpanFull()
                    ->label('وصف المهمة')
                    ->disabled(),
                Forms\Components\TextInput::make('progress')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required()
                    ->live() // <== أضف live() لتحديث حقل الحالة بناءً على التقدم
                    ->label('التقدم (%)'),
                Forms\Components\Select::make('status')
                    ->options([
                        'لم تبدأ' => 'لم تبدأ',
                        'قيد التنفيذ' => 'قيد التنفيذ',
                        'مكتملة' => 'مكتملة',
                        'متوقفة' => 'متوقفة',
                    ])
                    ->required()
                    ->label('الحالة')
                    // <== هنا التعديل: إضافة قاعدة التحقق المنطقية
                    ->rules([
                        fn (Forms\Get $get): Closure =>
                            function (string $attribute, $value, Closure $fail) use ($get) {
                                if ($value === 'مكتملة' && (int)$get('progress') < 100) {
                                    $fail('لا يمكن وضع حالة "مكتملة" إلا إذا كانت نسبة التقدم 100%.');
                                }
                                if ($value !== 'مكتملة' && (int)$get('progress') === 100) {
                                    $fail('إذا كانت نسبة التقدم 100%، فيجب أن تكون حالة المهمة "مكتملة".');
                                }
                            },
                    ]),
                Forms\Components\DatePicker::make('start_date')
                    ->label('تاريخ البدء المخطط')
                    ->disabled(),
                Forms\Components\DatePicker::make('end_date_planned')
                    ->label('تاريخ الانتهاء المخطط')
                    ->disabled(),
                Forms\Components\DatePicker::make('actual_end_date')
                    ->nullable()
                    ->label('تاريخ الانتهاء الفعلي')
                    ->visible(fn (Forms\Get $get) => $get('status') === 'مكتملة'),
                Forms\Components\Placeholder::make('estimated_cost_info')
                    ->content(fn (Task $record) => number_format($record->estimated_cost, 2) . ' SAR')
                    ->label('التكلفة التقديرية')
                    ->visible(fn (Task $record) => filled($record->estimated_cost)),
                Forms\Components\Placeholder::make('actual_cost_info')
                    ->content(fn (Task $record) => number_format($record->actual_cost, 2) . ' SAR')
                    ->label('التكلفة الفعلية')
                    ->visible(fn (Task $record) => filled($record->actual_cost)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->label('الوصف'),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('المشروع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('workshop.name')
                    ->label('الورشة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('progress')
                    ->label('التقدم (%)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'لم تبدأ' => 'info', 'قيد التنفيذ' => 'primary',
                        'مكتملة' => 'success', 'متوقفة' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date_planned')
                    ->label('تاريخ الانتهاء المخطط')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListTasks::route('/'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
            'view' => Pages\ViewTask::route('/{record}'),
        ];
    }
}