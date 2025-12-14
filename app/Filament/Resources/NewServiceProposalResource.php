<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewServiceProposalResource\Pages;
use App\Models\NewServiceProposal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;
use App\Models\User; // <== تأكد من استيراد User model
use Illuminate\Database\Eloquent\Builder; // <== تأكد من استيراد Builder

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
                    ->relationship('proposer', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('المستخدم المقترح')
                    ->disabledOn('edit'), // <== التعديل الرئيسي هنا: تعطيل الحقل عند التعديل
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
                    ->relationship('reviewer', 'first_name', fn (Builder $query) => 
                        $query->whereHas('roles', fn ($subQuery) => $subQuery->where('name', 'Reviewer')) // تصفية ليعرض المراجعين فقط
                    )
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
                Tables\Columns\TextColumn::make('proposer.name') // <== استخدام proposer.name accessor
                    ->label('المقترح')
                    // <== التعديل الرئيسي هنا: استخدام استعلام مخصص للبحث
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('proposer', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('proposal_date')
                    ->date()
                    ->sortable()
                    ->label('تاريخ الاقتراح'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'قيد المراجعة' => 'warning',
                        'تمت الموافقة' => 'success',
                        'مرفوض' => 'danger',
                        default => 'secondary',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reviewer.name') // <== استخدام reviewer.name accessor
                    ->label('المراجع')
                    // <== التعديل الرئيسي هنا: استخدام استعلام مخصص للبحث
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('reviewer', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
                    )
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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