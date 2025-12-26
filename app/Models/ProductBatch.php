<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'purchase_id',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'quantity',
        'remaining_quantity',
        'cost_price',
        'is_active',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'integer',
        'remaining_quantity' => 'integer',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'batch_id');
    }

    /**
     * Check if batch is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    /**
     * Check if batch is expiring soon (within specified days)
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isFuture() && $this->expiry_date->lte(Carbon::now()->addDays($days));
    }

    /**
     * Get days until expiry
     */
    public function daysUntilExpiry(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return Carbon::now()->diffInDays($this->expiry_date, false);
    }
}


