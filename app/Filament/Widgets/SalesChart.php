<?php

namespace App\Filament\Widgets;

use App\Enums\SaleStatus;
use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Évolution des ventes';
protected int|string|array $columnSpan = 'full';
    protected ?string $description =
        'Chiffre d’affaires des 30 derniers jours';

    protected function getData(): array
    {
        $startDate = now()
            ->subDays(29)
            ->startOfDay();

        /*
         * Récupération des ventes terminées
         * des 30 derniers jours.
         */
        $sales = Sale::query()
            ->where(
                'status',
                SaleStatus::COMPLETED->value
            )
            ->where(
                'sale_date',
                '>=',
                $startDate
            )
            ->selectRaw(
                'DATE(sale_date) as date, SUM(total) as total'
            )
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $labels = [];
        $data = [];

        /*
         * On crée les 30 jours.
         *
         * Même s'il n'y a aucune vente un jour donné,
         * le jour sera affiché avec une valeur de 0.
         */
        for ($i = 29; $i >= 0; $i--) {

            $date = now()
                ->subDays($i)
                ->format('Y-m-d');

            $labels[] = Carbon::parse($date)
                ->format('d/m');

            $data[] = (float) (
                $sales[$date] ?? 0
            );
        }

        return [
            'datasets' => [
                [
                    'label' => 'Chiffre d’affaires',
                    'data' => $data,
                ],
            ],

            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}