<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cv extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_details',
        // 'skills', // قم بإزالة هذا السطر من هنا أيضاً
        'experience',
        'education',
        'cv_status',
        'rejection_reason',
    ];

    // لا حاجة لـ casts for skills بعد الآن

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة Many-to-Many مع المهارات
    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'cv_skill');
    }
} 