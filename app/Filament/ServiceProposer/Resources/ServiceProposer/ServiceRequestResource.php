<?php

namespace App\Filament\ServiceProposer\Resources\ServiceProposer;

use App\Filament\ServiceProposer\Resources\ServiceProposer\ServiceRequestResource\Pages;
use App\Filament\ServiceProposer\Resources\ServiceProposer\ServiceRequestResource\RelationManagers;
use App\Models\ServiceRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Auth;

class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-hand-raised';
    protected static ?string $navigationLabel = 'طلباتي';
    protected static ?string $pluralModelLabel = 'طلباتي';
    protected static ?string $modelLabel = 'طلب خدمة';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('الخدمة المطلوبة'),
                RichEditor::make('details')
                    ->required()
                    ->columnSpanFull()
                    ->label('تفاصيل الطلب'),
                Forms\Components\Hidden::make('request_date')
                    ->default(now()->toDateString())
                    ->dehydrated(true),
                Forms\Components\Placeholder::make('requested_date_info')
                    ->content(now()->format('Y-m-d'))
                    ->label('تاريخ الطلب')
                    ->hiddenOn('edit'), // <== إخفاء هذا في صفحة التعديل
                
                // <== هنا التعديل: إخفاء هذه الحقول في صفحة الإنشاء (create)
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
                    ->label('حالة الطلب')
                    ->disabledOn('create') // لا يمكن للمستخدم تغيير الحالة عند الإنشاء
                    ->visibleOn('edit'), // <== يظهر فقط في صفحة التعديل
                Forms\Components\Placeholder::make('response_details_info')
                    ->content(fn (?ServiceRequest $record) => $record?->response_details ?? 'لا توجد استجابة بعد.') // <== تم تعديل Closure للسماح بـ null
                    ->label('تفاصيل الاستجابة')
                    ->visible(fn (?ServiceRequest $record) => filled($record?->response_details)) // <== يظهر فقط إذا كان السجل موجودًا والاستجابة موجودة
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
                Tables\Columns\TextColumn::make('service.name')
                    ->searchable()
                    ->sortable()
                    ->label('الخدمة'),
                Tables\Columns\TextColumn::make('details')
                    ->searchable()
                    ->limit(50)
                    ->html() // <== هنا التعديل: لعرض المحتوى كـ HTML
                    ->label('التفاصيل'),
                Tables\Columns\TextColumn::make('request_date')
                    ->date()
                    ->sortable()
                    ->label('تاريخ الطلب'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'قيد الانتظار' => 'warning', 'تمت الموافقة' => 'success',
                        'مرفوض' => 'danger', 'قيد التنفيذ' => 'info',
                        'مكتمل' => 'success', default => 'secondary',
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
            'index' => Pages\ListServiceRequests::route('/'),
            'create' => Pages\CreateServiceRequest::route('/create'),
            'view' => Pages\ViewServiceRequest::route('/{record}'),
        ];
    }
}