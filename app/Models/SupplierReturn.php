<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_no',
        'supplier_id',
        'user_id',
        'total_amount',
        'reason',
        'notes',
        'status',
        'return_date',
        'processed_date',
        'settlement_type',
        'refund_amount',
        'refund_date',
        'refund_method',
        'refund_notes',
        'is_settled',
        'settled_date',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'return_date' => 'date',
        'processed_date' => 'date',
        'refund_date' => 'date',
        'settled_date' => 'date',
        'is_settled' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierReturnItem::class);
    }

    public function replacements(): HasMany
    {
        return $this->hasMany(SupplierReplacement::class);
    }

    public function canBeProcessed(): bool
    {
        return $this->status === 'pending' || $this->status === 'approved';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isSettled(): bool
    {
        return $this->is_settled;
    }

    public function hasRefund(): bool
    {
        return $this->settlement_type === 'refund' && $this->is_settled;
    }

    public function hasReplacement(): bool
    {
        return $this->settlement_type === 'replacement' && $this->replacements()->count() > 0;
    }
}
