<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'reference',
        'sale_date',
        'status',
        'payment_method',
        'subtotal',
        'discount',
        'total',
        'amount_paid',
        'change_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'datetime',
            'status' => SaleStatus::class,
            'payment_method' => PaymentMethod::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns(): HasMany
{
    return $this->hasMany(ProductReturn::class);
}
}