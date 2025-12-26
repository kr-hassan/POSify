<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'category_id',
        'supplier_id',
        'cost_price',
        'sell_price',
        'stock',
        'alert_quantity',
        'tax_percent',
        'warranty_period_months',
        'is_active',
        // Medical fields
        'requires_prescription',
        'hsn_code',
        'manufacturer',
        'composition',
        'schedule',
        'shelf_life_days',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'stock' => 'integer',
        'alert_quantity' => 'integer',
        'tax_percent' => 'decimal:2',
        'warranty_period_months' => 'integer',
        'is_active' => 'boolean',
        'requires_prescription' => 'boolean',
        'shelf_life_days' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->alert_quantity;
    }

    /**
     * Get available batches (non-expired, with stock)
     */
    public function getAvailableBatchesAttribute()
    {
        return $this->batches()
            ->where('is_active', true)
            ->where('remaining_quantity', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>', now());
            })
            ->orderBy('expiry_date', 'asc')
            ->get();
    }
}


