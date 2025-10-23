<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewServiceProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposed_service_name',
        'service_details',
        'user_id',
        'proposal_date',
        'status',
        'reviewer_user_id',
        'review_comments',
    ];

    protected $casts = [
        'proposal_date' => 'date',
    ];

    public function proposer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}