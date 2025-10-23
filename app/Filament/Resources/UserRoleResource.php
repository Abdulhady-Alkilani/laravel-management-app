<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserRoleResource\Pages;
use App\Filament\Resources\UserRoleResource\RelationManagers;
use App\Models\UserRole;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserRoleResource extends Resource
{
    protected static ?string $model = UserRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'ربط المستخدم بالأدوار';
    protected static ?string $pluralModelLabel = 'روابط المستخدمين بالأدوار';
    protected static ?string $modelLabel = 'رابط مستخدم بدور';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'email') // أو 'name' إذا كان متاحاً
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('المستخدم'),
                Forms\Components\Select::make('role_id')
                    ->relationship('role', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('الدور'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name') // أو 'user.name'
                    ->searchable()
                    ->sortable()
                    ->label('اسم المستخدم'),
                Tables\Columns\TextColumn::make('user.email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('بريد المستخدم'),
                Tables\Columns\TextColumn::make('role.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->label('اسم الدور'),
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
            // هذا المورد لا يحتوي على علاقات أخرى مباشرة
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserRoles::route('/'),
            'create' => Pages\CreateUserRole::route('/create'),
            'edit' => Pages\EditUserRole::route('/{record}/edit'),
        ];
    }
}