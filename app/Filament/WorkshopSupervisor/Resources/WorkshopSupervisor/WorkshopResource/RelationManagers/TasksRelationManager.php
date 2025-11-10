<?php

namespace App\Filament\WorkshopSupervisor\Resources\WorkshopResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use App\Models\User; // لاستخدام Model المستخدم لتصفية العمال/المهندسين
use App\Models\Role; // لاستخدام Model الدور (لم نعد نحتاجه هنا مباشرة، يمكن إزالته إذا لم يستخدم)
use App\Models\Workshop; // لاستيراد Workshop model للحصول على العمال
use Closure; // لاستيراد Closure

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks'; // اسم العلاقة في Workshop model
    protected static ?string $title = 'المهام';
    protected static ?string $pluralTitle = 'المهام';
    protected static ?string $modelLabel = 'مهمة';
    protected static ?string $pluralModelLabel = 'مهام';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // === حقل project_id: يتم تعبئته تلقائياً من الورشة الأب ===
                Forms\Components\Hidden::make('project_id')
                    ->default($this->getOwnerRecord()->project_id)
                    ->dehydrated(true),
                Forms\Components\Placeholder::make('project_info')
                    ->content(fn () => $this->getOwnerRecord()->project->name ?? 'لا يوجد مشروع مرتبط')
                    ->label('المشروع التابع له المهمة')
                    ->columnSpanFull(),

                // === حقل workshop_id: يتم تعبئته تلقائياً بالورشة الأب ===
                Forms\Components\Hidden::make('workshop_id')
                    ->default($this->getOwnerRecord()->id)
                    ->dehydrated(true),
                Forms\Components\Placeholder::make('workshop_info')
                    ->content(fn () => $this->getOwnerRecord()->name)
                    ->label('الورشة التابعة للمهمة')
                    ->columnSpanFull(),

                RichEditor::make('description')
                    ->required()
                    ->columnSpanFull()
                    ->label('وصف المهمة')
                    ->helperText('وصف تفصيلي للمهمة المطلوبة.'),
                
                Forms\Components\TextInput::make('progress')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required()
                    ->default(0)
                    ->live() // مهم لتفعيل قواعد التحقق الديناميكية مع الحالة
                    ->label('التقدم (%)')
                    ->helperText('نسبة إنجاز المهمة (0-100%).'),
                
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
                    ->helperText('الحالة الحالية للمهمة.')
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
                    ->label('تاريخ البدء المخطط')
                    ->helperText('التاريخ المتوقع لبدء المهمة.'),
                
                Forms\Components\DatePicker::make('end_date_planned')
                    ->required()
                    ->label('تاريخ الانتهاء المخطط')
                    ->helperText('التاريخ المتوقع لانتهاء المهمة.'),
                
                Forms\Components\DatePicker::make('actual_end_date')
                    ->nullable()
                    ->label('تاريخ الانتهاء الفعلي')
                    ->helperText('التاريخ الفعلي الذي تم فيه إنجاز المهمة.')
                    ->visible(fn (Forms\Get $get) => $get('status') === 'مكتملة'), // يظهر فقط إذا كانت الحالة مكتملة
                
                Forms\Components\Select::make('assigned_to_user_id')
                    ->label('العامل المسؤول')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('اختر عاملاً أو مهندساً مرتبطاً بالورشة الحالية.')
                    ->options(function (): array {
                        $workshop = $this->getOwnerRecord(); // الحصول على الورشة الأب (الورشة الحالية)
                        if (!$workshop) {
                            return [];
                        }

                        $engineerRoles = [
                            'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
                            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
                            'Environmental Engineer', 'Surveying Engineer'
                        ];

                        // جلب العمال/المهندسين المرتبطين بهذه الورشة فقط
                        return $workshop->workers // علاقة workers في موديل Workshop
                            ->filter(fn (User $user) => 
                                $user->hasRole('Worker') || collect($engineerRoles)->contains(fn ($role) => $user->hasRole($role))
                            )
                            ->pluck('name', 'id') // 'name' accessor في User model
                            ->toArray();
                    })
                    ->getOptionLabelUsing(fn (?int $value): ?string => 
                        $value ? (User::find($value)?->name ?? 'غير معروف') : null
                    ),
                
                Forms\Components\TextInput::make('estimated_cost')
                    ->numeric()
                    ->nullable()
                    ->prefix('SR')
                    ->label('التكلفة التقديرية')
                    ->helperText('التكلفة المتوقعة لإنجاز المهمة.'),
                
                Forms\Components\TextInput::make('actual_cost')
                    ->numeric()
                    ->nullable()
                    ->prefix('SR')
                    ->label('التكلفة الفعلية')
                    ->helperText('التكلفة الفعلية التي تم صرفها على المهمة.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->label('الوصف'),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('المشروع')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('العامل المسؤول')
                    ->searchable()
                    ->sortable(),
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
}