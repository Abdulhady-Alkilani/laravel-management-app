<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    // علاقة Many-to-Many مع السيرة الذاتية
    public function cvs()
    {
        return $this->belongsToMany(Cv::class, 'cv_skill');
    }
} 