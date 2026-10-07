<?php

namespace App\Exports;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockReportExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
{
    public function __construct(
        protected string $period = 'month'
    ) {
    }

    public function query(): Builder
    {
        return ProductVariant::query()
            ->with([
                'product',
                'size',
                'color',
            ])
            ->where('is_active', true)
            ->orderBy('stock_quantity');
    }

    public function headings(): array
    {
        return [
            'Produit',
            'SKU',
            'Taille',
            'Couleur',
            'Stock',
            'Seuil',
            'Prix d’achat',
            'Prix de vente',
            'Valeur du stock',
            'Valeur potentielle',
            'Bénéfice potentiel',
            'État',
        ];
    }

    public function map($variant): array
    {
        $stockValue =
            $variant->stock_quantity *
            $variant->purchase_price;

        $potentialValue =
            $variant->stock_quantity *
            $variant->selling_price;

        $potentialProfit =
            $potentialValue -
            $stockValue;

        $status = match (true) {
            $variant->stock_quantity <= 0 => 'Rupture',
            $variant->stock_quantity <= $variant->alert_threshold => 'Stock faible',
            default => 'Normal',
        };

        return [
            $variant->product->name,

            $variant->sku,

            $variant->size?->name ?? '-',

            $variant->color?->name ?? '-',

            $variant->stock_quantity,

            $variant->alert_threshold,

            (float) $variant->purchase_price,

            (float) $variant->selling_price,

            (float) $stockValue,

            (float) $potentialValue,

            (float) $potentialProfit,

            $status,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}