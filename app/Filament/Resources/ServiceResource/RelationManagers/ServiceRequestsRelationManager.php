<?php

namespace App\Filament\Resources\ServiceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;

class ServiceRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceRequests'; // اسم العلاقة في Service model
    protected static ?string $title = 'طلبات الخدمات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('المستخدم مقدم الطلب'),
                RichEditor::make('details')
                    ->required()
                    ->columnSpanFull()
                    ->label('تفاصيل الطلب'),
                Forms\Components\DatePicker::make('request_date')
                    ->required()
                    ->label('تاريخ الطلب'),
                Forms\Components\Select::make('status')
                    ->options([
                        'قيد الانتظار' => 'قيد الانتظار',
                        'تمت الموافقة' => 'تمت الموافقة',
                        'مرفوض' => 'مرفوض',
                        'قيد التنفيذ' => 'قيد التنفيذ',
                        'مكتمل' => 'مكتمل',
                    ])
                    ->required()
                    ->default('قيد الانتظار')
                    ->label('حالة الطلب'),
                RichEditor::make('response_details')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('تفاصيل الاستجابة'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('details')
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')->label('المستخدم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('details')->label('التفاصيل')->limit(50),
                Tables\Columns\TextColumn::make('request_date')->label('تاريخ الطلب')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge()->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}