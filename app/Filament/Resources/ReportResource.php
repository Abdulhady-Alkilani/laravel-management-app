<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;
use App\Models\User;
use App\Models\Project;
use App\Models\Workshop;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Closure;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'التقارير';
    protected static ?string $pluralModelLabel = 'التقارير';
    protected static ?string $modelLabel = 'تقرير';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('workshop_id', null))
                    ->label('المشروع')
                    ->disabledOn('edit'),

                Forms\Components\Select::make('workshop_id')
                    ->relationship('workshop', 'name', fn (Builder $query, Forms\Get $get) =>
                        $query->when($get('project_id'), fn (Builder $query, $projectId) => $query->where('project_id', $projectId))
                    )
                    ->getOptionLabelFromRecordUsing(fn (Workshop $record) => $record->name)
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('employee_id', null))
                    ->label('الورشة')
                    ->disabledOn('edit'),
                
                Forms\Components\Select::make('employee_id')
                    ->label('الموظف مقدم التقرير')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->options(function (Forms\Get $get): array {
                        $workshopId = $get('workshop_id');
                        $projectId = $get('project_id');
                        $eligibleEmployees = collect();

                        $engineerAndWorkerRoles = [
                            'Worker', 'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
                            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
                            'Environmental Engineer', 'Surveying Engineer', 'Information Technology Engineer', 'Telecommunications Engineer',
                        ];

                        if ($workshopId) {
                            $workshop = Workshop::find($workshopId);
                            if ($workshop) {
                                if ($workshop->supervisor) {
                                    $eligibleEmployees->push($workshop->supervisor);
                                }
                                $eligibleEmployees = $eligibleEmployees->merge($workshop->workers->filter(fn (User $user) =>
                                    $user->hasRole('Worker') || collect($engineerAndWorkerRoles)->contains(fn ($role) => $user->hasRole($role))
                                ));
                            }
                        } elseif ($projectId) {
                            $project = Project::find($projectId);
                            if ($project) {
                                foreach ($project->workshops as $workshop) {
                                    if ($workshop->supervisor) {
                                        $eligibleEmployees->push($workshop->supervisor);
                                    }
                                    $eligibleEmployees = $eligibleEmployees->merge($workshop->workers->filter(fn (User $user) =>
                                        $user->hasRole('Worker') || collect($engineerAndWorkerRoles)->contains(fn ($role) => $user->hasRole($role))
                                    ));
                                }
                                if ($project->manager) {
                                    $eligibleEmployees->push($project->manager);
                                }
                            }
                        } else {
                            $eligibleEmployees = User::whereHas('roles', fn (Builder $roleQuery) => 
                                $roleQuery->whereIn('name', array_merge($engineerAndWorkerRoles, ['Workshop Supervisor', 'Manager']))
                            )->get();
                        }

                        return $eligibleEmployees
                                ->unique('id')
                                ->mapWithKeys(fn (User $user) => [$user->id => "{$user->first_name} {$user->last_name} ({$user->email})"])
                                ->toArray();
                    })
                    ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->disabledOn('edit'),

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
                
                // <== تم تصحيح هذا الحقل ليكون Forms\Components\Select في النموذج
                Forms\Components\Select::make('report_status')
                    ->options([
                        'معلقة' => 'معلقة',
                        'تمت المراجعة' => 'تمت المراجعة',
                        'مرفوض' => 'مرفوض',
                        'تمت الموافقة' => 'تمت الموافقة',
                    ])
                    ->required()
                    ->default('معلقة')
                    ->label('حالة التقرير'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('الموظف')
                    ->searchable(query: fn (Builder $query, string $search) =>
                        $query->whereHas('employee', fn (Builder $subQuery) =>
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                        )
            ),
                    // ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('المشروع')
                    ->searchable(query: fn (Builder $query, string $search) =>
                        $query->whereHas('project', fn (Builder $subQuery) =>
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('workshop.name')
                    ->label('الورشة')
                    ->searchable(query: fn (Builder $query, string $search) =>
                        $query->whereHas('workshop', fn (Builder $subQuery) =>
                            $subQuery->where('name', 'like', "%{$search}%")
                        )
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('الخدمة')
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
                // <== تم تصحيح هذا ليكون Tables\Columns\TextColumn في الجدول
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
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}