<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectInvestorLinkResource\Pages;
use App\Filament\Resources\ProjectInvestorLinkResource\RelationManagers;
use App\Models\ProjectInvestorLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User; // <== تأكد من استيراد User model
use Illuminate\Database\Eloquent\Builder; // <== تأكد من استيراد Builder

class ProjectInvestorLinkResource extends Resource
{
    protected static ?string $model = ProjectInvestorLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'روابط المستثمرين بالمشاريع';
    protected static ?string $pluralModelLabel = 'روابط المستثمرين بالمشاريع';
    protected static ?string $modelLabel = 'رابط مستثمر بمشروع';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('المشروع'),
                Forms\Components\Select::make('investor_user_id')
                    // <== التعديل الرئيسي هنا: تصفية المستخدمين ليعرض المستثمرين فقط
                    ->relationship('investor', 'first_name', fn (Builder $query) => 
                        $query->whereHas('roles', fn ($subQuery) => $subQuery->where('name', 'Investor'))
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('المستثمر'),
                Forms\Components\TextInput::make('investment_amount')
                    ->numeric()
                    ->nullable()
                    ->prefix('SYP')
                    ->label('مبلغ الاستثمار'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.name')
                    ->label('المشروع')
                    // <== استعلام بحث مخصص لـ project.name
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('project', fn (Builder $subQuery) => 
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('investor.name') // <== استخدام investor.name accessor
                    ->label('المستثمر')
                    // <== استعلام بحث مخصص لـ investor.name
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('investor', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('investment_amount')
                    ->money('SYP')
                    ->sortable()
                    ->label('مبلغ الاستثمار'),
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
            'index' => Pages\ListProjectInvestorLinks::route('/'),
            'create' => Pages\CreateProjectInvestorLink::route('/create'),
            'edit' => Pages\EditProjectInvestorLink::route('/{record}/edit'),
        ];
    }
}