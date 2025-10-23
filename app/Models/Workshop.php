<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'project_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function workerWorkshopLinks()
    {
        return $this->hasMany(WorkerWorkshopLink::class);
    }

    public function workers()
    {
        return $this->belongsToMany(User::class, 'worker_workshop_links', 'workshop_id', 'worker_id')
                    ->withPivot('assigned_date')
                    ->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}