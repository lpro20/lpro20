<?php

namespace App\Filament\Pages;

use App\Exports\StockReportExport;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class StockReport extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Rapports';

    protected static ?string $navigationLabel = 'Rapport du stock';

    protected static ?string $title = 'Rapport du stock';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected string $view = 'filament.pages.stock-report';

    public string $period = 'month';

    /*
    |--------------------------------------------------------------------------
    | Libellé période
    |--------------------------------------------------------------------------
    */

    public function getPeriodLabelProperty(): string
    {
        return match ($this->period) {
            'today' => "Aujourd'hui",
            'week' => 'Cette semaine',
            'month' => 'Ce mois',
            'year' => 'Cette année',
            'all' => 'Toutes les périodes',
            default => 'Ce mois',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Stock actuel
    |--------------------------------------------------------------------------
    */

    public function getStockQuery(): Builder
    {
        return ProductVariant::query()
            ->with([
                'product',
                'size',
                'color',
            ])
            ->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Quantité totale en stock
    |--------------------------------------------------------------------------
    */

    public function getTotalQuantityProperty(): int
    {
        return (int) $this->getStockQuery()->sum('stock_quantity');
    }

    /*
    |--------------------------------------------------------------------------
    | Valeur du stock au prix d'achat
    |--------------------------------------------------------------------------
    */

    public function getStockValueProperty(): float
    {
        return (float) (
            $this->getStockQuery()
                ->selectRaw(
                    'COALESCE(SUM(stock_quantity * purchase_price), 0) as value'
                )
                ->value('value') ?? 0
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Nombre de variantes
    |--------------------------------------------------------------------------
    */

    public function getVariantsCountProperty(): int
    {
        return $this->getStockQuery()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Produits sous le seuil
    |--------------------------------------------------------------------------
    */

    public function getLowStockCountProperty(): int
    {
        return $this->getStockQuery()
            ->whereColumn(
                'stock_quantity',
                '<=',
                'alert_threshold'
            )
            ->where('stock_quantity', '>', 0)
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Ruptures
    |--------------------------------------------------------------------------
    */

    public function getOutOfStockCountProperty(): int
    {
        return $this->getStockQuery()
            ->where('stock_quantity', '<=', 0)
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Valeur potentielle au prix de vente
    |--------------------------------------------------------------------------
    */

    public function getPotentialSalesValueProperty(): float
    {
        return (float) (
            $this->getStockQuery()
                ->selectRaw(
                    'COALESCE(SUM(stock_quantity * selling_price), 0) as value'
                )
                ->value('value') ?? 0
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Marge potentielle
    |--------------------------------------------------------------------------
    */

    public function getPotentialProfitProperty(): float
    {
        return $this->potentialSalesValue - $this->stockValue;
    }

    /*
    |--------------------------------------------------------------------------
    | Mouvements de la période
    |--------------------------------------------------------------------------
    */

    public function getMovementsQuery(): Builder
    {
        $query = StockMovement::query();

        return match ($this->period) {
            'today' => $query->whereDate('created_at', today()),

            'week' => $query->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ]),

            'month' => $query->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ]),

            'year' => $query->whereBetween('created_at', [
                now()->startOfYear(),
                now()->endOfYear(),
            ]),

            default => $query,
        };
    }

    public function getMovementsCountProperty(): int
    {
        return $this->getMovementsQuery()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Export Excel
    |--------------------------------------------------------------------------
    */

    public function exportExcel()
    {
           abort_unless(
        auth()->user()?->can('reports.export'),
        403
    );
    
        return Excel::download(
            new StockReportExport($this->period),
            'rapport-stock-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Export PDF
    |--------------------------------------------------------------------------
    */

    public function exportPdf()
    {
          abort_unless(
        auth()->user()?->can('reports.export'),
        403
    );

        $variants = $this->getStockQuery()
            ->orderBy('stock_quantity')
            ->get();

        $pdf = Pdf::loadView('exports.stock-report', [
            'variants' => $variants,
            'period' => $this->periodLabel,
            'totalQuantity' => $this->totalQuantity,
            'stockValue' => $this->stockValue,
            'variantsCount' => $this->variantsCount,
            'lowStockCount' => $this->lowStockCount,
            'outOfStockCount' => $this->outOfStockCount,
            'potentialSalesValue' => $this->potentialSalesValue,
            'potentialProfit' => $this->potentialProfit,
            'movementsCount' => $this->movementsCount,
        ]);

        return $pdf
            ->setPaper('a4', 'landscape')
            ->download(
                'rapport-stock-' . now()->format('Y-m-d') . '.pdf'
            );
    }





public static function canAccess(): bool
{
    return auth()->user()?->can('reports.view') ?? false;
}
}