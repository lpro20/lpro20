<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\SaleItem;
class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'size_id',
        'color_id',
        'sku',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'alert_threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function stockMovements(): HasMany
{
    return $this->hasMany(StockMovement::class);
}

public function isOutOfStock(): bool
{
    return $this->stock_quantity === 0;
}

public function isLowStock(): bool
{
    return $this->stock_quantity > 0
        && $this->stock_quantity <= $this->alert_threshold;
}

public function getStockValueAttribute(): float
{
    return $this->stock_quantity * (float) $this->purchase_price;
}

public function purchaseItems(): HasMany
{
    return $this->hasMany(PurchaseItem::class);
}

public function saleItems(): HasMany
{
    return $this->hasMany(SaleItem::class);
}
}