<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


use App\Models\ProductVariant;
use App\Models\Sale;
class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_variant_id',
        'quantity',
        'unit_price',
        'cost_price',
        'total',
        'profit',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'total' => 'decimal:2',
            'profit' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function returns(): HasMany
{
    return $this->hasMany(ProductReturn::class);
}
}