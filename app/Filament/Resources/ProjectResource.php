<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;
use App\Models\User; // <== تأكد من استيراد User model
use Illuminate\Database\Eloquent\Builder; // <== تأكد من استيراد Builder

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'المشاريع';
    protected static ?string $pluralModelLabel = 'المشاريع';
    protected static ?string $modelLabel = 'مشروع';

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
                    ->prefix('SYP')
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
                Forms\Components\Select::make('manager_user_id')
                    // <== هذا الجزء هو الذي يضمن عرض مديري المشاريع فقط
                    ->relationship('manager', 'first_name', fn (Builder $query) => 
                        $query->whereHas('roles', fn ($subQuery) => $subQuery->where('name', 'Manager'))
                    )
                    ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('مدير المشروع المسؤول'),
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
                Tables\Columns\TextColumn::make('manager.name')
                    ->label('مدير المشروع')
                    // <== استعلام بحث مخصص لمدير المشروع
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('manager', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('budget')
                    ->money('SYP')
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