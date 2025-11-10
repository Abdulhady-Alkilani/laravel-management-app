<?php

namespace App\Filament\ServiceProposer\Resources\ServiceProposer;

use App\Filament\ServiceProposer\Resources\ServiceProposer\NewServiceProposalResource\Pages;
use App\Filament\ServiceProposer\Resources\ServiceProposer\NewServiceProposalResource\RelationManagers;
use App\Models\NewServiceProposal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Auth;

class NewServiceProposalResource extends Resource
{
    protected static ?string $model = NewServiceProposal::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';
    protected static ?string $navigationLabel = 'اقتراحاتي';
    protected static ?string $pluralModelLabel = 'اقتراحاتي';
    protected static ?string $modelLabel = 'مقترح خدمة';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

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
                Forms\Components\Hidden::make('proposal_date')
                    ->default(now()->toDateString())
                    ->dehydrated(true),
                Forms\Components\Placeholder::make('proposal_date_info')
                    ->content(now()->format('Y-m-d'))
                    ->label('تاريخ تقديم الاقتراح')
                    ->hiddenOn('edit'), // <== إخفاء هذا في صفحة التعديل
                
                // <== هنا التعديلات: إخفاء هذه الحقول في صفحة الإنشاء (create) والسماح بـ null
                Forms\Components\Select::make('status')
                    ->options([
                        'قيد المراجعة' => 'قيد المراجعة',
                        'تمت الموافقة' => 'تمت الموافقة',
                        'مرفوض' => 'مرفوض',
                    ])
                    ->required()
                    ->default('قيد المراجعة')
                    ->label('حالة الاقتراح')
                    ->disabledOn('create') // لا يمكن للمستخدم تغيير الحالة عند الإنشاء
                    ->visibleOn('edit'), // <== يظهر فقط في صفحة التعديل
                Forms\Components\Placeholder::make('reviewer_info')
                    ->content(fn (?NewServiceProposal $record) => $record?->reviewer->name ?? 'لم يتم المراجعة بعد.') // <== تم تعديل Closure للسماح بـ null
                    ->label('المراجع')
                    ->hiddenOn('create'), // <== إخفاء هذا في صفحة الإنشاء
                Forms\Components\Placeholder::make('review_comments_info')
                    ->content(fn (?NewServiceProposal $record) => $record?->review_comments ?? 'لا توجد تعليقات.') // <== تم تعديل Closure للسماح بـ null
                    ->label('تعليقات المراجع')
                    ->visible(fn (?NewServiceProposal $record) => filled($record?->review_comments)) // <== يظهر فقط إذا كان السجل موجودًا والاستجابة موجودة
                    ->hiddenOn('create'), // <== إخفاء هذا في صفحة الإنشاء
                
                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id())
                    ->dehydrated(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('proposed_service_name')
                    ->searchable()
                    ->sortable()
                    ->label('اسم المقترح'),
                Tables\Columns\TextColumn::make('proposal_date')
                    ->date()
                    ->sortable()
                    ->label('تاريخ الاقتراح'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'قيد المراجعة' => 'warning', 'تمت الموافقة' => 'success',
                        'مرفوض' => 'danger', default => 'secondary',
                    })
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListNewServiceProposals::route('/'),
            'create' => Pages\CreateNewServiceProposal::route('/create'),
            'view' => Pages\ViewNewServiceProposal::route('/{record}'),
        ];
    }
}