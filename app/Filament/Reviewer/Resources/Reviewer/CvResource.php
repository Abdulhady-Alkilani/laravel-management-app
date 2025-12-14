<?php

namespace App\Filament\Reviewer\Resources\Reviewer;

use App\Filament\Reviewer\Resources\Reviewer\CvResource\Pages;
use App\Filament\Reviewer\Resources\Reviewer\CvResource\RelationManagers;
use App\Models\Cv;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use App\Models\User; // تأكد من استيراد User model

class CvResource extends Resource
{
    protected static ?string $model = Cv::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'مراجعة السير الذاتية';
    protected static ?string $pluralModelLabel = 'السير الذاتية للمراجعة';
    protected static ?string $modelLabel = 'سيرة ذاتية';

    protected static bool $canCreate = false;
    protected static bool $canEdit = true;
    protected static bool $canDelete = false;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('cv_status', ['قيد الانتظار', 'تمت الموافقة', 'مرفوض'])
            ->orderByDesc('created_at');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('user_info')
                    ->content(fn (Cv $record) => $record->user->name . ' (' . $record->user->email . ')')
                    ->label('صاحب السيرة الذاتية'),
                Forms\Components\Textarea::make('profile_details')
                    ->columnSpanFull()
                    ->label('تفاصيل الملف الشخصي')
                    ->disabled(),
                Forms\Components\Textarea::make('experience')
                    ->columnSpanFull()
                    ->label('الخبرات')
                    ->disabled(),
                Forms\Components\Textarea::make('education')
                    ->columnSpanFull()
                    ->label('المؤهلات العلمية')
                    ->disabled(),
                Forms\Components\Select::make('skills_list')
                    ->multiple()
                    ->relationship('skills', 'name')
                    ->preload()
                    ->disabled()
                    ->label('المهارات'),
                Forms\Components\Select::make('cv_status')
                    ->options([
                        'تمت الموافقة' => 'تمت الموافقة',
                        'مرفوض' => 'مرفوض',
                        'قيد الانتظار' => 'قيد الانتظار',
                    ])
                    ->required()
                    ->label('تغيير حالة السيرة الذاتية'),
                Forms\Components\Textarea::make('rejection_reason')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('تعليقات المراجعة / سبب الرفض')
                    ->helperText('يمكنك إضافة تعليق للمراجعة أو سبب الرفض هنا.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('صاحب السيرة')
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('user', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
            ), // <== التعديل الرئيسي هنا
                    // ->sortable(),
                Tables\Columns\TextColumn::make('experience')
                    ->label('الخبرة')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('education')
                    ->label('التعليم')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('cv_status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'قيد الانتظار' => 'warning',
                        'تمت الموافقة' => 'success',
                        'مرفوض' => 'danger',
                        default => 'secondary',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('skills.name')
                    ->label('المهارات')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->label('التعليقات/سبب الرفض')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ التقديم'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cv_status')
                    ->options([
                        'قيد الانتظار' => 'قيد الانتظار',
                        'تمت الموافقة' => 'تمت الموافقة',
                        'مرفوض' => 'مرفوض',
                    ])
                    ->label('حالة السيرة الذاتية'),
            ])
            ->actions([
                Action::make('approve')
                    ->label('موافقة')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->nullable()
                            ->label('تعليقات المراجعة')
                            ->helperText('أضف أي تعليقات عند الموافقة.'),
                    ])
                    ->action(function (Cv $record, array $data) {
                        $record->cv_status = 'تمت الموافقة';
                        $record->rejection_reason = $data['rejection_reason'];
                        $record->save();
                    })
                    ->visible(fn (Cv $record) => $record->cv_status === 'قيد الانتظار'),
                Action::make('reject')
                    ->label('رفض')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->label('سبب الرفض')
                            ->helperText('الرجاء توضيح سبب الرفض.'),
                    ])
                    ->action(function (Cv $record, array $data) {
                        $record->cv_status = 'مرفوض';
                        $record->rejection_reason = $data['rejection_reason'];
                        $record->save();
                    })
                    ->visible(fn (Cv $record) => $record->cv_status === 'قيد الانتظار'),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SkillsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCvs::route('/'),
            'view' => Pages\ViewCv::route('/{record}'),
            'edit' => Pages\EditCv::route('/{record}/edit'),
        ];
    }
}