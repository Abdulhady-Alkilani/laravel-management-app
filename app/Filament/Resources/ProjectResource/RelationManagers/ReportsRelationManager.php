<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;
use App\Models\User; // <== تأكد من استيراد User model
use App\Models\Workshop; // <== تأكد من استيراد Workshop model
use App\Models\Service; // <== تأكد من استيراد Service model
use Illuminate\Database\Eloquent\Builder; // <== تأكد من استيراد Builder
use Closure; // <== تأكد من استيراد Closure إذا كنت تستخدمها في قواعد التحقق

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';
    protected static ?string $title = 'التقارير التابعة'; // إضافة لغة عربية
    protected static ?string $pluralTitle = 'التقارير التابعة'; // إضافة لغة عربية
    protected static ?string $modelLabel = 'تقرير';          // إضافة لغة عربية
    protected static ?string $pluralModelLabel = 'تقارير';    // إضافة لغة عربية

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // === حقل project_id: يتم تعبئته تلقائياً من المشروع الأب (غير قابل للتعديل) ===
                Forms\Components\Hidden::make('project_id')
                    ->default($this->getOwnerRecord()->id)
                    ->dehydrated(true),
                Forms\Components\Placeholder::make('project_info')
                    ->content(fn () => $this->getOwnerRecord()->name)
                    ->label('المشروع التابع له التقرير')
                    ->columnSpanFull(),

                Forms\Components\Select::make('workshop_id')
                    // <== التعديل الرئيسي هنا: تصفية الورش لتظهر فقط التابعة للمشروع الحالي
                    ->relationship('workshop', 'name', fn (Builder $query) => $query->where('project_id', $this->getOwnerRecord()->id))
                    ->getOptionLabelFromRecordUsing(fn (Workshop $record) => $record->name)
                    ->searchable()
                    ->preload()
                    ->nullable() // يمكن أن يكون التقرير عن مشروع وليس ورشة محددة
                    ->live() // <== مهم جداً لتشغيل التحديث الديناميكي
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('employee_id', null)) // <== إعادة تعيين الموظف عند تغيير الورشة
                    ->label('الورشة'),
                
                Forms\Components\Select::make('employee_id')
                    ->label('الموظف مقدم التقرير')
                    ->searchable()
                    ->preload()
                    ->required()
                    // <== التعديل الرئيسي هنا: تصفية الموظفين بناءً على الورشة المختارة
                    ->options(function (Forms\Get $get): array {
                        $workshopId = $get('workshop_id');
                        if (!$workshopId) {
                            // إذا لم يتم اختيار ورشة، يمكن عرض مديري الورش فقط (المشرف على الورشة)
                            // أو لا شيء، حسب منطق العمل. هنا سنعرض مشرف الورشة
                            $workshop = $this->getOwnerRecord()->workshops->first(fn($w) => $w->id == $workshopId);
                            if ($workshop && $workshop->supervisor) {
                                return [$workshop->supervisor->id => "{$workshop->supervisor->first_name} {$workshop->supervisor->last_name} (مشرف الورشة)"];
                            }
                            return [];
                        }

                        $workshop = Workshop::find($workshopId);
                        if (!$workshop) { return []; }

                        $engineerRoles = [
                            'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
                            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
                            'Environmental Engineer', 'Surveying Engineer'
                        ];
                        
                        // جمع العمال/المهندسين في الورشة ومشرف الورشة
                        $eligibleEmployees = collect();
                        if ($workshop->supervisor) {
                            $eligibleEmployees->push($workshop->supervisor); // إضافة مشرف الورشة
                        }

                        $workersInWorkshop = $workshop->workers;
                        $eligibleEmployees = $eligibleEmployees->merge($workersInWorkshop->filter(function ($user) use ($engineerRoles) {
                            return $user->hasRole('Worker') || collect($engineerRoles)->contains(fn ($role) => $user->hasRole($role));
                        }));

                        return $eligibleEmployees->unique('id')->pluck('name', 'id')->toArray(); // 'name' accessor في User model
                    })
                    ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->first_name} {$record->last_name} ({$record->email})"),
                
                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('الخدمة'),
                
                Forms\Components\TextInput::make('report_type')
                    ->required()
                    ->maxLength(255)
                    ->label('نوع التقرير'),
                RichEditor::make('report_details')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('تفاصيل التقرير'),
                
                Forms\Components\TextInput::make('report_status')
                    ->maxLength(255)
                    ->nullable()
                    ->label('حالة التقرير'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('report_type')
            ->columns([
                Tables\Columns\TextColumn::make('employee.name') // <== استخدام employee.name accessor
                    ->label('الموظف')
                    // <== استعلام بحث مخصص لـ employee.name
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('employee', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
            ),
                    // ->sortable(),
                Tables\Columns\TextColumn::make('workshop.name')
                    ->label('الورشة')
                    // <== استعلام بحث مخصص لـ workshop.name
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('workshop', fn (Builder $subQuery) => 
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('الخدمة')
                    // <== استعلام بحث مخصص لـ service.name
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('service', fn (Builder $subQuery) => 
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_type')
                    ->searchable()
                    ->sortable()
                    ->label('نوع التقرير'),
                Tables\Columns\TextColumn::make('report_status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'معلقة' => 'warning', 'تمت المراجعة' => 'info',
                        'تمت الموافقة' => 'success', 'مرفوض' => 'danger',
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
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
            // هذه الصفحات غير مستخدمة في RelationManager
            // 'index' => Pages\ListReports::route('/'),
            // 'create' => Pages\CreateReport::route('/create'),
            // 'edit' => Pages\EditReport::route('/{record}/edit'),
            // 'view' => Pages\ViewReport::route('/{record}'),
        ];
    }
}