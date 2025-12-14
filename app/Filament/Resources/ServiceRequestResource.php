<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceRequestResource\Pages;
use App\Models\ServiceRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;
use App\Models\User; // <== تأكد من استيراد User model
use App\Models\Service; // <== تأكد من استيراد Service model
use Illuminate\Database\Eloquent\Builder; // <== تأكد من استيراد Builder

class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-hand-raised';
    protected static ?string $navigationLabel = 'طلبات الخدمات';
    protected static ?string $pluralModelLabel = 'طلبات الخدمات';
    protected static ?string $modelLabel = 'طلب خدمة';

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
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('المستخدم مقدم الطلب')
                    ->disabledOn('edit'), // <== التعديل الرئيسي هنا: تعطيل الحقل عند التعديل
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service.name')
                    ->label('الخدمة')
                    // <== استعلام بحث مخصص لـ service.name
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('service', fn (Builder $subQuery) => 
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name') // <== استخدام user.name accessor
                    ->label('مقدم الطلب')
                    // <== استعلام بحث مخصص لـ user.name
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('user', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('details')
                    ->searchable()
                    ->limit(50)
                    ->html()
                    ->label('التفاصيل'),
                Tables\Columns\TextColumn::make('request_date')
                    ->date()
                    ->sortable()
                    ->label('تاريخ الطلب'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'قيد الانتظار' => 'warning',
                        'تمت الموافقة' => 'success',
                        'مرفوض' => 'danger',
                        'قيد التنفيذ' => 'primary',
                        'مكتمل' => 'info',
                        default => 'secondary',
                    })
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
            'index' => Pages\ListServiceRequests::route('/'),
            'create' => Pages\CreateServiceRequest::route('/create'),
            'edit' => Pages\EditServiceRequest::route('/{record}/edit'),
        ];
    }
}