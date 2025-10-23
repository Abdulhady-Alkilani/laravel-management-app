<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'budget',
        'start_date',
        'end_date_planned',
        'end_date_actual',
        'status',
        'manager_user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date_planned' => 'date',
        'end_date_actual' => 'date',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function workshops()
    {
        return $this->hasMany(Workshop::class);
    }

    public function projectInvestorLinks()
    {
        return $this->hasMany(ProjectInvestorLink::class);
    }

    public function investors()
    {
        return $this->belongsToMany(User::class, 'project_investor_links', 'project_id', 'investor_user_id')
                    ->withPivot('investment_amount')
                    ->withTimestamps();
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}