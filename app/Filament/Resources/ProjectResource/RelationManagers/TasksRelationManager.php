<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Task;
use App\Models\User; // <== تأكد من استيراد User model
use App\Models\Workshop; // <== تأكد من استيراد Workshop model
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Auth; // <== تأكد من استيراد Auth إذا كنت تستخدمه هنا (ليس ضرورياً في هذا السياق)

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';
    protected static ?string $title = 'المهام التابعة';
    protected static ?string $pluralTitle = 'المهام التابعة'; // إضافة لغة عربية
    protected static ?string $modelLabel = 'مهمة';          // إضافة لغة عربية
    protected static ?string $pluralModelLabel = 'مهام';    // إضافة لغة عربية


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
                    ->label('المشروع التابع له المهمة')
                    ->columnSpanFull(),

                Forms\Components\Select::make('workshop_id')
                    // <== التعديل الرئيسي هنا: تصفية الورش لتظهر فقط التابعة للمشروع الحالي
                    ->relationship('workshop', 'name', fn (Builder $query) => $query->where('project_id', $this->getOwnerRecord()->id))
                    ->getOptionLabelFromRecordUsing(fn (Workshop $record) => $record->name)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live() // <== مهم جداً لتشغيل التحديث الديناميكي
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('assigned_to_user_id', null)) // <== إعادة تعيين العامل عند تغيير الورشة
                    ->label('الورشة'),
                
                RichEditor::make('description')
                    ->required()
                    ->columnSpanFull()
                    ->label('وصف المهمة'),
                
                Forms\Components\TextInput::make('progress')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required()
                    ->default(0)
                    ->live() // <== مهم لتفعيل قواعد التحقق الديناميكية مع الحالة
                    ->label('التقدم (%)'),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'لم تبدأ' => 'لم تبدأ',
                        'قيد التنفيذ' => 'قيد التنفيذ',
                        'مكتملة' => 'مكتملة',
                        'متوقفة' => 'متوقفة',
                    ])
                    ->required()
                    ->default('لم تبدأ')
                    ->label('الحالة')
                    ->rules([ // <== قواعد التحقق التي تربط التقدم بالحالة
                        fn (Forms\Get $get): Closure =>
                            function (string $attribute, $value, Closure $fail) use ($get) {
                                if ($value === 'مكتملة' && (int)$get('progress') < 100) {
                                    $fail('لا يمكن وضع حالة "مكتملة" إلا إذا كانت نسبة التقدم 100%.');
                                }
                                if ($value !== 'مكتملة' && (int)$get('progress') === 100) {
                                    $fail('إذا كانت نسبة التقدم 100%، فيجب أن تكون حالة المهمة "مكتملة".');
                                }
                            },
                    ]),
                
                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->label('تاريخ البدء المخطط'),
                
                Forms\Components\DatePicker::make('end_date_planned')
                    ->required()
                    ->label('تاريخ الانتهاء المخطط'),
                
                Forms\Components\DatePicker::make('actual_end_date')
                    ->nullable()
                    ->label('تاريخ الانتهاء الفعلي')
                    ->visible(fn (Forms\Get $get) => $get('status') === 'مكتملة'), // <== يظهر فقط إذا كانت الحالة مكتملة
                
                Forms\Components\Select::make('assigned_to_user_id')
                    ->label('العامل المسؤول')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('اختر عاملاً أو مهندساً مرتبطاً بالورشة المختارة.')
                    // <== التعديل الرئيسي هنا: تصفية العمال/المهندسين بناءً على الورشة المختارة
                    ->options(function (Forms\Get $get): array {
                        $workshopId = $get('workshop_id');
                        if (!$workshopId) { return []; } // إذا لم يتم اختيار ورشة، لا تعرض أي عمال

                        $workersInWorkshop = Workshop::find($workshopId)?->workers;
                        if (!$workersInWorkshop) { return []; }

                        $engineerRoles = [
                            'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
                            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
                            'Environmental Engineer', 'Surveying Engineer'
                        ];

                        // تصفية العمال الذين ينتمون إلى الورشة المختارة ولديهم دور "Worker" أو دور هندسي
                        return $workersInWorkshop->filter(function ($user) use ($engineerRoles) {
                            return $user->hasRole('Worker') || collect($engineerRoles)->contains(fn ($role) => $user->hasRole($role));
                        })->pluck('name', 'id')->toArray(); // 'name' هنا يجب أن يكون accessor في موديل User
                    })
                    ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->first_name} {$record->last_name} ({$record->email})"),
                
                Forms\Components\TextInput::make('estimated_cost')
                    ->numeric()
                    ->nullable()
                    ->prefix('SYP')
                    ->label('التكلفة التقديرية'),
                
                Forms\Components\TextInput::make('actual_cost')
                    ->numeric()
                    ->nullable()
                    ->prefix('SYP')
                    ->label('التكلفة الفعلية'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->html()
                    ->limit(50)
                    ->label('الوصف'),
                Tables\Columns\TextColumn::make('workshop.name')
                    ->label('الورشة')
                    // <== استعلام بحث مخصص لـ workshop.name
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('workshop', fn (Builder $subQuery) => 
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedTo.name') // <== استخدام assignedTo.name accessor
                    ->label('العامل المسؤول')
                    // <== استعلام بحث مخصص لـ assignedTo.name
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->whereHas('assignedTo', fn (Builder $subQuery) => 
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
            ),
                    // ->sortable(),
                Tables\Columns\TextColumn::make('progress')
                    ->label('التقدم (%)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'لم تبدأ' => 'info', 'قيد التنفيذ' => 'primary',
                        'مكتملة' => 'success', 'متوقفة' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date_planned')
                    ->label('تاريخ الانتهاء المخطط')
                    ->date()
                    ->sortable(),
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
            // 'index' => Pages\ListTasks::route('/'), // هذه الصفحات غير مستخدمة في RelationManager
            // 'create' => Pages\CreateTask::route('/create'),
            // 'edit' => Pages\EditTask::route('/{record}/edit'),
            // 'view' => Pages\ViewTask::route('/{record}'),
        ];
    }
}