<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerWorkshopLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'workshop_id',
        'assigned_date',
    ];

    protected $casts = [
        'assigned_date' => 'date',
    ];

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }
}