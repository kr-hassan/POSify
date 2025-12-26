<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'batch_id',
        'quantity',
        'price',
        'total',
        'returned_quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'returned_quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function warranty(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Warranty::class);
    }

    /**
     * Get all return items for this sale item
     */
    public function returnItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }

    /**
     * Get the quantity available for return (quantity - returned_quantity)
     */
    public function getAvailableForReturnAttribute(): int
    {
        return max(0, $this->quantity - $this->returned_quantity);
    }

    /**
     * Check if this item can be returned
     */
    public function getCanBeReturnedAttribute(): bool
    {
        return $this->available_for_return > 0;
    }
}


