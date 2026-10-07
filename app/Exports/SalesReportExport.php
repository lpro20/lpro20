<?php

// namespace App\Exports;

// use App\Enums\SaleStatus;
// use App\Models\Sale;
// use Illuminate\Database\Eloquent\Builder;
// use Maatwebsite\Excel\Concerns\FromQuery;
// use Maatwebsite\Excel\Concerns\ShouldAutoSize;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\WithMapping;
// use Maatwebsite\Excel\Concerns\WithStyles;
// use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// class SalesReportExport implements
//     FromQuery,
//     WithHeadings,
//     WithMapping,
//     ShouldAutoSize,
//     WithStyles
// {
//     public function __construct(
//         protected string $period = 'month'
//     ) {
//     }

//     public function query(): Builder
//     {
//         return Sale::query()
//             ->with([
//                 'customer',
//                 'user',
//             ])
//             ->where('status', SaleStatus::COMPLETED->value)
//             ->when(
//                 $this->period === 'today',
//                 fn (Builder $query) =>
//                     $query->whereDate('sale_date', today())
//             )
//             ->when(
//                 $this->period === 'week',
//                 fn (Builder $query) =>
//                     $query->whereBetween('sale_date', [
//                         now()->startOfWeek(),
//                         now()->endOfWeek(),
//                     ])
//             )
//             ->when(
//                 $this->period === 'month',
//                 fn (Builder $query) =>
//                     $query->whereBetween('sale_date', [
//                         now()->startOfMonth(),
//                         now()->endOfMonth(),
//                     ])
//             )
//             ->when(
//                 $this->period === 'year',
//                 fn (Builder $query) =>
//                     $query->whereBetween('sale_date', [
//                         now()->startOfYear(),
//                         now()->endOfYear(),
//                     ])
//             )
//             ->latest('sale_date');
//     }

//     public function headings(): array
//     {
//         return [
//             'Référence',
//             'Date',
//             'Client',
//             'Vendeur',
//             'Mode de paiement',
//             'Sous-total',
//             'Remise',
//             'Total',
//             'Montant payé',
//             'Monnaie rendue',
//         ];
//     }

//     public function map($sale): array
//     {
//         return [
//             $sale->reference,

//             $sale->sale_date
//                 ? $sale->sale_date->format('d/m/Y H:i')
//                 : '',

//             $sale->customer?->name ?? 'Client comptant',

//             $sale->user?->name ?? '',

//             $sale->payment_method,

//             (float) $sale->subtotal,

//             (float) $sale->discount,

//             (float) $sale->total,

//             (float) $sale->amount_paid,

//             (float) $sale->change_amount,
//         ];
//     }

//     public function styles(Worksheet $sheet): array
//     {
//         return [
//             1 => [
//                 'font' => [
//                     'bold' => true,
//                 ],
//             ],
//         ];
//     }
// }








namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
{
    public function __construct(
        protected Builder $query
    ) {}

    public function query(): Builder
    {
        return $this->query
            ->with([
                'customer',
                'user',
            ])
            ->orderByDesc('sale_date');
    }

    public function headings(): array
    {
        return [
            'Référence',
            'Date',
            'Client',
            'Vendeur',
            'Mode de paiement',
            'Sous-total',
            'Remise',
            'Total',
            'Montant payé',
            'Monnaie',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->reference,

            optional($sale->sale_date)
                ? $sale->sale_date->format('d/m/Y H:i')
                : '-',

            $sale->customer?->name
                ?? 'Client comptant',

            $sale->user?->name
                ?? '-',

            $sale->payment_method instanceof \App\Enums\PaymentMethod
                ? $sale->payment_method->label()
                : (string) $sale->payment_method,

            (float) $sale->subtotal,

            (float) $sale->discount,

            (float) $sale->total,

            (float) $sale->amount_paid,

            (float) $sale->change_amount,
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