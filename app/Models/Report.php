<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'project_id',
        'workshop_id',
        'service_id',
        'report_type',
        'report_details',
        'report_status',
    ];

    // protected $casts = ['report_details' => 'json']; // إذا كان report_details سيخزن كـ JSON

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}