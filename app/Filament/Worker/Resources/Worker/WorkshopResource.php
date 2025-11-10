<?php

namespace App\Filament\Worker\Resources\Worker;

use App\Filament\Worker\Resources\Worker\WorkshopResource\Pages;
use App\Filament\Worker\Resources\Worker\WorkshopResource\RelationManagers;
use App\Models\Workshop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class WorkshopResource extends Resource
{
    protected static ?string $model = Workshop::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'ورشاتي';
    protected static ?string $pluralModelLabel = 'ورشاتي';
    protected static ?string $modelLabel = 'ورشة';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('workers', fn (Builder $query) => $query->where('users.id', Auth::id()));
    }

    // <== تعطيل صلاحيات الإنشاء، التعديل، الحذف بشكل صارم
    protected static bool $canCreate = false;
    protected static bool $canEdit = false;
    protected static bool $canDelete = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('name')
                    ->label('اسم الورشة'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->label('وصف الورشة'),
                Forms\Components\Placeholder::make('project_info')
                    ->content(fn (Workshop $record) => $record->project->name ?? 'لا يوجد مشروع')
                    ->label('المشروع المرتبط'),
                Forms\Components\Placeholder::make('supervisor_info')
                    ->content(fn (Workshop $record) => $record->supervisor->name ?? 'لا يوجد مشرف')
                    ->label('مشرف الورشة'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('اسم الورشة'),
                Tables\Columns\TextColumn::make('project.name')
                    ->searchable()
                    ->sortable()
                    ->label('المشروع'),
                Tables\Columns\TextColumn::make('supervisor.name')
                    ->searchable()
                    ->sortable()
                    ->label('مشرف الورشة'),
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
            'index' => Pages\ListWorkshops::route('/'),
            // 'create' => Pages\CreateWorkshop::route('/create'), // <== تم تعطيل صفحة الإنشاء
            // 'edit' => Pages\EditWorkshop::route('/{record}/edit'), // <== تم تعطيل صفحة التعديل
            'view' => Pages\ViewWorkshop::route('/{record}'),
        ];
    }
}