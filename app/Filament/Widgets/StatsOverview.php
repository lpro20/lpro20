<?php

namespace App\Filament\Widgets;

use App\Enums\SaleStatus;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        /*
         * Périodes
         */
        $today = now()->startOfDay();

        $monthStart = now()->startOfMonth();

        /*
         * =========================
         * CHIFFRE D'AFFAIRES DU JOUR
         * =========================
         */
        $todaySales = Sale::query()
            ->where(
                'status',
                SaleStatus::COMPLETED->value
            )
            ->where(
                'sale_date',
                '>=',
                $today
            )
            ->sum('total');

        /*
         * =========================
         * CHIFFRE D'AFFAIRES DU MOIS
         * =========================
         */
        $monthSales = Sale::query()
            ->where(
                'status',
                SaleStatus::COMPLETED->value
            )
            ->where(
                'sale_date',
                '>=',
                $monthStart
            )
            ->sum('total');

        /*
         * =========================
         * NOMBRE DE VENTES DU JOUR
         * =========================
         */
        $todayOrders = Sale::query()
            ->where(
                'status',
                SaleStatus::COMPLETED->value
            )
            ->where(
                'sale_date',
                '>=',
                $today
            )
            ->count();

        /*
         * =========================
         * BÉNÉFICE DU MOIS
         *
         * Bénéfice =
         * prix de vente - prix d'achat
         * =========================
         */
    $monthProfit = SaleItem::query()
    ->whereHas('sale', function ($query) use ($monthStart) {
        $query
            ->where(
                'status',
                SaleStatus::COMPLETED->value
            )
            ->where(
                'sale_date',
                '>=',
                $monthStart
            );
    })
    ->sum('profit');

        /*
         * =========================
         * PRODUITS SOUS LE SEUIL
         * =========================
         */
        $lowStock = ProductVariant::query()
            ->whereColumn(
                'stock_quantity',
                '<=',
                'alert_threshold'
            )
            ->count();

        /*
         * =========================
         * STOCK TOTAL
         * =========================
         */
        $totalStock = ProductVariant::query()
            ->sum('stock_quantity');

        /*
         * =========================
         * VALEUR DU STOCK
         *
         * stock × prix d'achat
         * =========================
         */
        $stockValue = ProductVariant::query()
            ->selectRaw(
                'SUM(
                    stock_quantity * purchase_price
                ) as value'
            )
            ->value('value') ?? 0;

        return [

            /*
             * CA JOUR
             */
            Stat::make(
                'Chiffre d’affaires aujourd’hui',
                number_format(
                    $todaySales,
                    0,
                    ',',
                    ' '
                ) . ' FCFA'
            )
                ->description(
                    "{$todayOrders} vente(s)"
                )
                ->descriptionIcon(
                    'heroicon-o-banknotes'
                )
                ->color('success'),

            /*
             * CA MOIS
             */
            Stat::make(
                'Chiffre d’affaires du mois',
                number_format(
                    $monthSales,
                    0,
                    ',',
                    ' '
                ) . ' FCFA'
            )
                ->description(
                    'Depuis le début du mois'
                )
                ->descriptionIcon(
                    'heroicon-o-chart-bar'
                )
                ->color('primary'),

            /*
             * BÉNÉFICE
             */
            Stat::make(
                'Bénéfice du mois',
                number_format(
                    $monthProfit,
                    0,
                    ',',
                    ' '
                ) . ' FCFA'
            )
                ->description(
                    'Marge brute estimée'
                )
                ->descriptionIcon(
                    'heroicon-o-arrow-trending-up'
                )
                ->color('success'),

            /*
             * STOCK
             */
            Stat::make(
                'Valeur du stock',
                number_format(
                    $stockValue,
                    0,
                    ',',
                    ' '
                ) . ' FCFA'
            )
                ->description(
                    $totalStock . ' article(s) en stock'
                )
                ->descriptionIcon(
                    'heroicon-o-cube'
                )
                ->color('primary'),

            /*
             * ALERTES
             */
            Stat::make(
                'Alertes de stock',
                $lowStock
            )
                ->description(
                    'variante(s) sous le seuil'
                )
                ->descriptionIcon(
                    'heroicon-o-exclamation-triangle'
                )
                ->color(
                    $lowStock > 0
                        ? 'danger'
                        : 'success'
                ),
        ];
    }
}