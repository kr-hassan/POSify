<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ProductReturn Model
 * 
 * Represents a product return (refund or exchange).
 * This is separate from repair workflow and handles financial + inventory adjustments.
 */
class ProductReturn extends Model
{
    use HasFactory;

    protected $table = 'returns'; // Use 'returns' table

    protected $fillable = [
        'return_no',
        'sale_id',
        'customer_id',
        'return_type', // refund or exchange
        'total_refund',
        'status', // pending, approved, rejected, processed
        'created_by',
        'approved_by',
        'reason',
        'return_date',
        'processed_date',
    ];

    protected $casts = [
        'total_refund' => 'decimal:2',
        'return_date' => 'date',
        'processed_date' => 'date',
    ];

    /**
     * Get the sale this return is associated with
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the customer who made the return
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created this return
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this return
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all return items
     */
    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    /**
     * Check if return is pending approval
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if return is approved
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if return is processed
     */
    public function getIsProcessedAttribute(): bool
    {
        return $this->status === 'processed';
    }

    /**
     * Check if return is a refund
     */
    public function getIsRefundAttribute(): bool
    {
        return $this->return_type === 'refund';
    }

    /**
     * Check if return is an exchange
     */
    public function getIsExchangeAttribute(): bool
    {
        return $this->return_type === 'exchange';
    }
}


