<?php

namespace App\Models;

use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReturn extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'sale_id',
        'sale_item_id',
        'product_variant_id',
        'user_id',
        'reference',
        'quantity',
        'reason',
        'restock',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reason' => ReturnReason::class,
            'status' => ReturnStatus::class,
            'quantity' => 'integer',
            'restock' => 'boolean',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}