<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectInvestorLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'investor_user_id',
        'investment_amount',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function investor()
    {
        return $this->belongsTo(User::class, 'investor_user_id');
    }
}