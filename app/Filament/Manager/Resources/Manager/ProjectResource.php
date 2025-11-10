<?php

namespace App\Filament\Manager\Resources\Manager;

use App\Filament\Manager\Resources\Manager\ProjectResource\Pages;
use App\Filament\Manager\Resources\Manager\ProjectResource\RelationManagers; // <== تأكد من وجود هذا الاستيراد
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'مشاريعي';
    protected static ?string $pluralModelLabel = 'مشاريعي';
    protected static ?string $modelLabel = 'مشروع';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('manager_user_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->label('اسم المشروع'),
                RichEditor::make('description')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('وصف المشروع'),
                Forms\Components\TextInput::make('location')
                    ->required()
                    ->maxLength(255)
                    ->label('الموقع الجغرافي'),
                Forms\Components\TextInput::make('budget')
                    ->numeric()
                    ->required()
                    ->prefix('SR')
                    ->label('الميزانية المخصصة'),
                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->label('تاريخ البدء المخطط'),
                Forms\Components\DatePicker::make('end_date_planned')
                    ->required()
                    ->label('تاريخ الانتهاء المخطط'),
                Forms\Components\DatePicker::make('end_date_actual')
                    ->nullable()
                    ->label('تاريخ الانتهاء الفعلي'),
                Forms\Components\Select::make('status')
                    ->options([
                        'مخطط' => 'مخطط',
                        'قيد التنفيذ' => 'قيد التنفيذ',
                        'مكتمل' => 'مكتمل',
                        'متوقف' => 'متوقف',
                    ])
                    ->required()
                    ->default('مخطط')
                    ->label('حالة المشروع'),
                Forms\Components\Hidden::make('manager_user_id')
                    ->default(auth()->id())
                    ->dehydrated(true),
                Forms\Components\Placeholder::make('manager_info')
                    ->content(fn () => auth()->user()->name)
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
                Tables\Columns\TextColumn::make('budget')
                    ->money('SAR')
                    ->sortable()
                    ->label('الميزانية'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'مخطط' => 'info',
                        'قيد التنفيذ' => 'primary',
                        'مكتمل' => 'success',
                        'متوقف' => 'danger',
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
                Tables\Columns\TextColumn::make('overdue_tasks_count')
                    ->getStateUsing(fn (Project $record) => $record->tasks()->where('end_date_planned', '<', now())->whereNotIn('status', ['مكتملة', 'متوقفة'])->count())
                    ->label('مهام متأخرة')
                    ->color('danger')
                    ->sortable(),
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
            RelationManagers\WorkshopsRelationManager::class,
            RelationManagers\InvestorsRelationManager::class,
            RelationManagers\TasksRelationManager::class,
            RelationManagers\ReportsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}