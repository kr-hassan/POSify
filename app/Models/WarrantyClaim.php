<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_no',
        'warranty_id',
        'user_id',
        'claim_type',
        'issue_description',
        'status',
        'resolution_notes',
        'claim_date',
        'resolved_date',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'resolved_date' => 'date',
    ];

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
