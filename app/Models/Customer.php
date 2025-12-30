<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'balance',
        // Patient fields
        'gender',
        'date_of_birth',
        'blood_group',
        'allergies',
        'medical_history',
        'is_patient',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'date_of_birth' => 'date',
        'is_patient' => 'boolean',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all warranties for this customer
     */
    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class);
    }

    /**
     * Get all product returns for this customer
     */
    public function productReturns(): HasMany
    {
        return $this->hasMany(ProductReturn::class);
    }

    /**
     * Calculate age from date of birth
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }
        return $this->date_of_birth->age;
    }
}


