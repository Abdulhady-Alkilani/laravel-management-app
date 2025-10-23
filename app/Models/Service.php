<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}