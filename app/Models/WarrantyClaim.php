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
        'received_date', // When product was received for repair
        'returned_date', // When repaired product was returned to customer
        'technician_notes', // Notes from technician during repair
    ];

    protected $casts = [
        'claim_date' => 'date',
        'resolved_date' => 'date',
        'received_date' => 'date',
        'returned_date' => 'date',
    ];

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if claim is for repair
     */
    public function getIsRepairAttribute(): bool
    {
        return $this->claim_type === 'repair';
    }

    /**
     * Check if claim is pending
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if claim is in progress (repair workflow)
     */
    public function getIsInProgressAttribute(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if claim is completed
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }
}
