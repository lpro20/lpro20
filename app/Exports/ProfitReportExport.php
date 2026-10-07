<?php

namespace App\Exports;

use App\Enums\SaleStatus;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitReportExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
{
    public function __construct(
        protected string $period = 'month',
        protected array $filters = [],
        protected string $search = ''
    ) {}

    public function query(): Builder
    {
        $query = SaleItem::query()
            ->with([
                'sale.customer',
                'sale.user',
                'productVariant.product',
                'productVariant.size',
                'productVariant.color',
            ])
            ->whereHas('sale', function (Builder $query) {
                $query->where(
                    'status',
                    SaleStatus::COMPLETED->value
                );

                match ($this->period) {
                    'today' => $query->whereDate(
                        'sale_date',
                        today()
                    ),

                    'week' => $query->whereBetween('sale_date', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ]),

                    'month' => $query->whereBetween('sale_date', [
                        now()->startOfMonth(),
                        now()->endOfMonth(),
                    ]),

                    'year' => $query->whereBetween('sale_date', [
                        now()->startOfYear(),
                        now()->endOfYear(),
                    ]),

                    default => null,
                };
            });

        /*
        |--------------------------------------------------------------------------
        | Filtre type de bénéfice
        |--------------------------------------------------------------------------
        */

        $profitType = $this->filters['profit_type']['value'] ?? null;

        match ($profitType) {
            'positive' => $query->where('profit', '>', 0),
            'zero' => $query->where('profit', '=', 0),
            'negative' => $query->where('profit', '<', 0),
            default => null,
        };

        /*
        |--------------------------------------------------------------------------
        | Filtre produit
        |--------------------------------------------------------------------------
        */

        $productId = $this->filters['product']['value'] ?? null;

        if ($productId) {
            $query->whereHas(
                'productVariant',
                fn (Builder $query) =>
                    $query->where('product_id', $productId)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Période personnalisée
        |--------------------------------------------------------------------------
        */

        $from = $this->filters['custom_date']['from'] ?? null;
        $until = $this->filters['custom_date']['until'] ?? null;

        if ($from || $until) {
            $query->whereHas(
                'sale',
                function (Builder $query) use (
                    $from,
                    $until
                ) {
                    $query
                        ->when(
                            $from,
                            fn (
                                Builder $query,
                                $date
                            ) => $query->whereDate(
                                'sale_date',
                                '>=',
                                $date
                            )
                        )
                        ->when(
                            $until,
                            fn (
                                Builder $query,
                                $date
                            ) => $query->whereDate(
                                'sale_date',
                                '<=',
                                $date
                            )
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Recherche
        |--------------------------------------------------------------------------
        */

        if (filled($this->search)) {
            $search = '%' . $this->search . '%';

            $query->where(function (Builder $query) use ($search) {
                $query
                    ->whereHas(
                        'sale',
                        function (Builder $query) use ($search) {
                            $query
                                ->where(
                                    'reference',
                                    'like',
                                    $search
                                )
                                ->orWhereHas(
                                    'customer',
                                    fn (
                                        Builder $query
                                    ) => $query->where(
                                        'name',
                                        'like',
                                        $search
                                    )
                                );
                        }
                    )
                    ->orWhereHas(
                        'productVariant',
                        function (Builder $query) use ($search) {
                            $query
                                ->where(
                                    'sku',
                                    'like',
                                    $search
                                )
                                ->orWhereHas(
                                    'product',
                                    fn (
                                        Builder $query
                                    ) => $query->where(
                                        'name',
                                        'like',
                                        $search
                                    )
                                );
                        }
                    );
            });
        }

        return $query->latest('id');
    }

    public function headings(): array
    {
        return [
            'Référence',
            'Date',
            'Client',
            'Vendeur',
            'Produit',
            'SKU',
            'Quantité',
            'Prix de vente',
            'Prix d’achat',
            'Chiffre d’affaires',
            'Coût',
            'Bénéfice',
        ];
    }

    public function map($item): array
    {
        return [
            $item->sale->reference,

            $item->sale->sale_date
                ? $item->sale->sale_date->format('d/m/Y H:i')
                : '',

            $item->sale->customer?->name
                ?? 'Client comptant',

            $item->sale->user?->name
                ?? '',

            $item->productVariant->product->name,

            $item->productVariant->sku,

            $item->quantity,

            (float) $item->unit_price,

            (float) $item->cost_price,

            (float) $item->total,

            (float) (
                $item->cost_price *
                $item->quantity
            ),

            (float) $item->profit,
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