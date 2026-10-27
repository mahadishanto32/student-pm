<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerApproval extends Model
{
    use HasFactory;

    protected $table = 'partner_approvals';

    protected $fillable = [
        'project_id',
        'user_id',
        'invited_by',
        'approve',
        'for_creation',
    ];

    protected $casts = [
        'for_creation' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopePending($query)
    {
        return $query->where('approve', 'Pending');
    }
}