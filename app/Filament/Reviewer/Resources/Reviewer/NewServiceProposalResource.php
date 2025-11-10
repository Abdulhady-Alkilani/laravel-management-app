<?php

namespace App\Filament\Reviewer\Resources\Reviewer;

use App\Filament\Reviewer\Resources\Reviewer\NewServiceProposalResource\Pages;
use App\Filament\Reviewer\Resources\Reviewer\NewServiceProposalResource\RelationManagers;
use App\Models\NewServiceProposal;
use App\Models\Service; // لاستخدامها عند الموافقة على اقتراح الخدمة
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Actions\Action;

class NewServiceProposalResource extends Resource
{
    protected static ?string $model = NewServiceProposal::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';
    protected static ?string $navigationLabel = 'مراجعة المقترحات';
    protected static ?string $pluralModelLabel = 'مقترحات الخدمات الجديدة للمراجعة';
    protected static ?string $modelLabel = 'مقترح خدمة';

    // <== تعطيل صلاحيات الإنشاء والحذف بالكامل
    protected static bool $canCreate = false;
    protected static bool $canDelete = false;

    // <== تصفية المقترحات التي تحتاج للمراجعة
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('status', ['قيد المراجعة', 'تمت الموافقة', 'مرفوض']) // عرض جميع الحالات بعد المراجعة أيضاً
            ->orderByDesc('proposal_date');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('proposed_service_name')
                    ->columnSpanFull()
                    ->label('اسم الخدمة المقترحة')
                    ->disabled(), // <== للقراءة فقط
                RichEditor::make('service_details')
                    ->columnSpanFull()
                    ->label('تفاصيل الخدمة المقترحة')
                    ->disabled(), // <== للقراءة فقط
                Forms\Components\Placeholder::make('proposer_info')
                    ->content(fn (NewServiceProposal $record) => $record->proposer->name ?? 'N/A')
                    ->label('المقترح من قبل'),
                Forms\Components\DatePicker::make('proposal_date')
                    ->label('تاريخ تقديم الاقتراح')
                    ->disabled(), // <== للقراءة فقط
                // حقول المراجعة (للتعديل)
                Forms\Components\Select::make('status')
                    ->options([
                        'تمت الموافقة' => 'تمت الموافقة',
                        'مرفوض' => 'مرفوض',
                        'قيد المراجعة' => 'قيد المراجعة',
                    ])
                    ->required()
                    ->label('تغيير حالة الاقتراح'),
                Forms\Components\Textarea::make('review_comments') // <== حقل التعليقات يظهر دائماً في نموذج التعديل
                    ->columnSpanFull()
                    ->nullable()
                    ->label('تعليقات المراجع'),
                Forms\Components\Hidden::make('reviewer_user_id') // لتعيين المراجع الحالي تلقائياً عند التعديل
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
                Tables\Columns\TextColumn::make('proposer.name')
                    ->label('المقترح من')
                    ->searchable()
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
                Tables\Columns\TextColumn::make('reviewer.name') // <== لعرض اسم المراجع
                    ->label('المراجع')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('review_comments') // <== لعرض التعليقات
                    ->label('التعليقات')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ الإنشاء'),
            ])
            ->filters([
                // يمكن إضافة فلتر لحالة الاقتراح
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'قيد المراجعة' => 'قيد المراجعة',
                        'تمت الموافقة' => 'تمت الموافقة',
                        'مرفوض' => 'مرفوض',
                    ])
                    ->label('حالة الاقتراح'),
            ])
            ->actions([
                // إجراءات الموافقة/الرفض
                Action::make('approve')
                    ->label('موافقة')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('review_comments') // <== إضافة حقل التعليق هنا
                            ->nullable()
                            ->label('تعليقات المراجعة')
                            ->helperText('أضف أي تعليقات عند الموافقة.'),
                    ])
                    ->action(function (NewServiceProposal $record, array $data) {
                        $record->status = 'تمت الموافقة';
                        $record->reviewer_user_id = Auth::id();
                        $record->review_comments = $data['review_comments'];
                        $record->save();

                        Service::firstOrCreate(
                            ['name' => $record->proposed_service_name],
                            ['description' => $record->service_details, 'status' => 'نشطة']
                        );
                    })
                    ->visible(fn (NewServiceProposal $record) => $record->status === 'قيد المراجعة'),
                Action::make('reject')
                    ->label('رفض')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('review_comments') // <== إضافة حقل التعليق هنا
                            ->required()
                            ->label('سبب الرفض')
                            ->helperText('الرجاء توضيح سبب الرفض.'),
                    ])
                    ->action(function (NewServiceProposal $record, array $data) {
                        $record->status = 'مرفوض';
                        $record->reviewer_user_id = Auth::id();
                        $record->review_comments = $data['review_comments'];
                        $record->save();
                    })
                    ->visible(fn (NewServiceProposal $record) => $record->status === 'قيد المراجعة'),
                Tables\Actions\ViewAction::make(), // <== عرض التفاصيل
                Tables\Actions\EditAction::make(), // <== للتعديل على الحالة والتعليقات
            ])
            ->bulkActions([
                // لا توجد إجراءات مجمعة في هذه الحالة
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
            'view' => Pages\ViewNewServiceProposal::route('/{record}'),
            'edit' => Pages\EditNewServiceProposal::route('/{record}/edit'),
        ];
    }
}