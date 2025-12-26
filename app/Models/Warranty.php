<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Warranty extends Model
{
    use HasFactory;

    protected $fillable = [
        'warranty_no',
        'sale_id',
        'sale_item_id',
        'customer_id',
        'product_id',
        'start_date',
        'end_date',
        'warranty_period_months',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'warranty_period_months' => 'integer',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warrantyClaims(): HasMany
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && $this->end_date >= Carbon::today();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date < Carbon::today();
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->is_expired) {
            return 0;
        }
        return Carbon::today()->diffInDays($this->end_date, false);
    }
}
