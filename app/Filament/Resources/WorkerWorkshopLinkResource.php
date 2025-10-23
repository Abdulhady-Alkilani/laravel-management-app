<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkerWorkshopLinkResource\Pages;
use App\Filament\Resources\WorkerWorkshopLinkResource\RelationManagers;
use App\Models\WorkerWorkshopLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkerWorkshopLinkResource extends Resource
{
    protected static ?string $model = WorkerWorkshopLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationLabel = 'ربط العمال بالورشات';
    protected static ?string $pluralModelLabel = 'روابط العمال بالورشات';
    protected static ?string $modelLabel = 'رابط عامل بورشة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('worker_id')
                    ->relationship('worker', 'first_name') // 'worker' هو اسم العلاقة في Model WorkerWorkshopLink
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('العامل'),
                Forms\Components\Select::make('workshop_id')
                    ->relationship('workshop', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('الورشة'),
                Forms\Components\DatePicker::make('assigned_date')
                    ->nullable()
                    ->label('تاريخ التعيين'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('worker.first_name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->label('العامل'),
                Tables\Columns\TextColumn::make('workshop.name')
                    ->searchable()
                    ->sortable()
                    ->label('الورشة'),
                Tables\Columns\TextColumn::make('assigned_date')
                    ->date()
                    ->sortable()
                    ->label('تاريخ التعيين'),
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
            'index' => Pages\ListWorkerWorkshopLinks::route('/'),
            'create' => Pages\CreateWorkerWorkshopLink::route('/create'),
            'edit' => Pages\EditWorkerWorkshopLink::route('/{record}/edit'),
        ];
    }
}