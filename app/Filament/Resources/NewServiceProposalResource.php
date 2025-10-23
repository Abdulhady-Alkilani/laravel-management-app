<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewServiceProposalResource\Pages;
use App\Filament\Resources\NewServiceProposalResource\RelationManagers;
use App\Models\NewServiceProposal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;

class NewServiceProposalResource extends Resource
{
    protected static ?string $model = NewServiceProposal::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';
    protected static ?string $navigationLabel = 'مقترحات الخدمات الجديدة';
    protected static ?string $pluralModelLabel = 'مقترحات الخدمات الجديدة';
    protected static ?string $modelLabel = 'مقترح خدمة جديد';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('proposed_service_name')
                    ->required()
                    ->maxLength(255)
                    ->label('اسم الخدمة المقترحة'),
                RichEditor::make('service_details')
                    ->required()
                    ->columnSpanFull()
                    ->label('تفاصيل الخدمة المقترحة'),
                Forms\Components\Select::make('user_id')
                    ->relationship('proposer', 'first_name') // 'proposer' هي علاقة BelongsTo في Model NewServiceProposal
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('المستخدم المقترح'),
                Forms\Components\DatePicker::make('proposal_date')
                    ->required()
                    ->label('تاريخ تقديم الاقتراح'),
                Forms\Components\Select::make('status')
                    ->options([
                        'قيد المراجعة' => 'قيد المراجعة',
                        'تمت الموافقة' => 'تمت الموافقة',
                        'مرفوض' => 'مرفوض',
                    ])
                    ->required()
                    ->default('قيد المراجعة')
                    ->label('حالة الاقتراح'),
                Forms\Components\Select::make('reviewer_user_id')
                    ->relationship('reviewer', 'first_name') // 'reviewer' هي علاقة BelongsTo في Model NewServiceProposal
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('المراجع'),
                RichEditor::make('review_comments')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('تعليقات المراجع'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('proposed_service_name')
                    ->searchable()
                    ->sortable()
                    ->label('اسم الخدمة المقترحة'),
                Tables\Columns\TextColumn::make('proposer.first_name')
                    ->label('المقترح')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('proposal_date')
                    ->date()
                    ->sortable()
                    ->label('تاريخ الاقتراح'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reviewer.first_name')
                    ->label('المراجع')
                    ->searchable(['first_name', 'last_name'])
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
            'index' => Pages\ListNewServiceProposals::route('/'),
            'create' => Pages\CreateNewServiceProposal::route('/create'),
            'edit' => Pages\EditNewServiceProposal::route('/{record}/edit'),
        ];
    }
}