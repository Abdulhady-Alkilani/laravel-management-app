<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'workshop_id',
        'description',
        'progress',
        'start_date',
        'end_date_planned',
        'actual_end_date',
        'assigned_to_user_id',
        'status',
        'estimated_cost',
        'actual_cost',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date_planned' => 'date',
        'actual_end_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}