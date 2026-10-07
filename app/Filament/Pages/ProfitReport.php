<?php

// namespace App\Filament\Pages;

// use App\Enums\SaleStatus;
// use App\Exports\ProfitReportExport;
// use App\Models\SaleItem;
// use Barryvdh\DomPDF\Facade\Pdf;
// use Filament\Forms\Components\DatePicker;
// use Filament\Pages\Page;
// use Filament\Tables;
// use Filament\Tables\Columns\TextColumn;
// use Filament\Tables\Filters\Filter;
// use Filament\Tables\Filters\SelectFilter;
// use Filament\Tables\Table;
// use Illuminate\Database\Eloquent\Builder;
// use Maatwebsite\Excel\Facades\Excel;

// class ProfitReport extends Page implements Tables\Contracts\HasTable
// {
//     use Tables\Concerns\InteractsWithTable;

//     //     protected static ?string $navigationGroup = 'Rapports';
// protected static string|\UnitEnum|null $navigationGroup = 'Rapports';

//     protected static ?string $navigationLabel = 'Rapport des bénéfices';

//     protected static ?string $title = 'Rapport des bénéfices';

//    //     protected static ?string $navigationIcon = 'heroicon-o-banknotes';
//  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

//     protected string $view = 'filament.pages.profit-report';

//     public string $period = 'month';

//     public function getPeriodLabelProperty(): string
//     {
//         return match ($this->period) {
//             'today' => "Aujourd'hui",
//             'week' => 'Cette semaine',
//             'month' => 'Ce mois',
//             'year' => 'Cette année',
//             'all' => 'Toutes les périodes',
//             default => 'Ce mois',
//         };
//     }

//     /**
//      * Requête principale des ventes de la période.
//      */
//     public function getSalesQuery(): Builder
//     {
//         $query = \App\Models\Sale::query()
//             ->where('status', SaleStatus::COMPLETED->value);

//         return match ($this->period) {
//             'today' => $query->whereDate('sale_date', today()),

//             'week' => $query->whereBetween('sale_date', [
//                 now()->startOfWeek(),
//                 now()->endOfWeek(),
//             ]),

//             'month' => $query->whereBetween('sale_date', [
//                 now()->startOfMonth(),
//                 now()->endOfMonth(),
//             ]),

//             'year' => $query->whereBetween('sale_date', [
//                 now()->startOfYear(),
//                 now()->endOfYear(),
//             ]),

//             default => $query,
//         };
//     }

//     /**
//      * Requête des lignes de vente utilisées pour calculer les bénéfices.
//      */
//     public function getItemsQuery(): Builder
//     {
//         return SaleItem::query()
//             ->whereHas('sale', function (Builder $query) {
//                 $query->where(
//                     'status',
//                     SaleStatus::COMPLETED->value
//                 );

//                 match ($this->period) {
//                     'today' => $query->whereDate(
//                         'sale_date',
//                         today()
//                     ),

//                     'week' => $query->whereBetween('sale_date', [
//                         now()->startOfWeek(),
//                         now()->endOfWeek(),
//                     ]),

//                     'month' => $query->whereBetween('sale_date', [
//                         now()->startOfMonth(),
//                         now()->endOfMonth(),
//                     ]),

//                     'year' => $query->whereBetween('sale_date', [
//                         now()->startOfYear(),
//                         now()->endOfYear(),
//                     ]),

//                     default => null,
//                 };
//             });
//     }

//     /**
//      * Table détaillée des bénéfices.
//      */
//     public function table(Table $table): Table
//     {
//         return $table
//             ->query(
//                 SaleItem::query()
//                     ->with([
//                         'sale.customer',
//                         'productVariant.product',
//                         'productVariant.size',
//                         'productVariant.color',
//                     ])
//                     ->whereHas('sale', function (Builder $query) {
//                         $query->where(
//                             'status',
//                             SaleStatus::COMPLETED->value
//                         );

//                         match ($this->period) {
//                             'today' => $query->whereDate(
//                                 'sale_date',
//                                 today()
//                             ),

//                             'week' => $query->whereBetween('sale_date', [
//                                 now()->startOfWeek(),
//                                 now()->endOfWeek(),
//                             ]),

//                             'month' => $query->whereBetween('sale_date', [
//                                 now()->startOfMonth(),
//                                 now()->endOfMonth(),
//                             ]),

//                             'year' => $query->whereBetween('sale_date', [
//                                 now()->startOfYear(),
//                                 now()->endOfYear(),
//                             ]),

//                             default => null,
//                         };
//                     })
//             )

//             ->columns([
//                 TextColumn::make('sale.reference')
//                     ->label('Référence')
//                     ->searchable()
//                     ->sortable()
//                     ->copyable(),

//                 TextColumn::make('sale.sale_date')
//                     ->label('Date')
//                     ->dateTime('d/m/Y H:i')
//                     ->sortable(),

//                 TextColumn::make('sale.customer.name')
//                     ->label('Client')
//                     ->formatStateUsing(
//                         fn ($state) => $state ?: 'Client comptant'
//                     )
//                     ->searchable()
//                     ->sortable(),

//                 TextColumn::make('productVariant.product.name')
//                     ->label('Produit')
//                     ->searchable()
//                     ->sortable()
//                     ->description(
//                         fn (SaleItem $record) =>
//                             'SKU : ' . $record->productVariant->sku
//                     ),

//                 TextColumn::make('quantity')
//                     ->label('Qté')
//                     ->numeric()
//                     ->sortable(),

//                 TextColumn::make('unit_price')
//                     ->label('Prix vente')
//                     ->numeric(decimalPlaces: 0)
//                     ->suffix(' FCFA')
//                     ->sortable(),

//                 TextColumn::make('cost_price')
//                     ->label('Prix achat')
//                     ->numeric(decimalPlaces: 0)
//                     ->suffix(' FCFA')
//                     ->sortable(),

//                 TextColumn::make('total')
//                     ->label('Chiffre d’affaires')
//                     ->numeric(decimalPlaces: 0)
//                     ->suffix(' FCFA')
//                     ->sortable(),

//                 TextColumn::make('profit')
//                     ->label('Bénéfice')
//                     ->numeric(decimalPlaces: 0)
//                     ->suffix(' FCFA')
//                     ->weight('bold')
//                     ->color(
//                         fn ($state) => $state > 0
//                             ? 'success'
//                             : ($state < 0 ? 'danger' : 'gray')
//                     )
//                     ->sortable(),
//             ])

//             ->filters([
//                 SelectFilter::make('profit_type')
//                     ->label('Type de bénéfice')
//                     ->options([
//                         'positive' => 'Bénéfice positif',
//                         'zero' => 'Bénéfice nul',
//                         'negative' => 'Perte',
//                     ])
//                     ->query(function (
//                         Builder $query,
//                         array $data
//                     ): Builder {
//                         return match ($data['value'] ?? null) {
//                             'positive' => $query->where('profit', '>', 0),
//                             'zero' => $query->where('profit', '=', 0),
//                             'negative' => $query->where('profit', '<', 0),
//                             default => $query,
//                         };
//                     }),

//                 Filter::make('sale_date')
//                     ->label('Période personnalisée')
//                     ->schema([
//                         DatePicker::make('from')
//                             ->label('Du'),

//                         DatePicker::make('until')
//                             ->label("Jusqu'au"),
//                     ])
//                     ->columns(2)
//                     ->query(function (
//                         Builder $query,
//                         array $data
//                     ): Builder {
//                         return $query->whereHas(
//                             'sale',
//                             function (Builder $query) use ($data) {
//                                 $query
//                                     ->when(
//                                         $data['from'] ?? null,
//                                         fn (
//                                             Builder $query,
//                                             $date
//                                         ) => $query->whereDate(
//                                             'sale_date',
//                                             '>=',
//                                             $date
//                                         )
//                                     )
//                                     ->when(
//                                         $data['until'] ?? null,
//                                         fn (
//                                             Builder $query,
//                                             $date
//                                         ) => $query->whereDate(
//                                             'sale_date',
//                                             '<=',
//                                             $date
//                                         )
//                                     );
//                             }
//                         );
//                     }),

//                 SelectFilter::make('product')
//                     ->label('Produit')
//                     ->relationship(
//                         'productVariant.product',
//                         'name'
//                     )
//                     ->searchable()
//                     ->preload(),
//             ])

//             ->defaultSort(
//                 'sale.sale_date',
//                 'desc'
//             )

//             ->searchPlaceholder(
//                 'Rechercher une vente, un client ou un produit...'
//             )

//             ->paginated([
//                 10,
//                 25,
//                 50,
//                 100,
//             ]);
//     }

//     /**
//      * Chiffre d'affaires.
//      */
//     public function getRevenueProperty(): float
//     {
//         return (float) $this->getSalesQuery()->sum('total');
//     }

//     /**
//      * Coût total des marchandises vendues.
//      */
//     public function getCostProperty(): float
//     {
//         return (float) (
//             $this->getItemsQuery()
//                 ->selectRaw(
//                     'COALESCE(SUM(cost_price * quantity), 0) as total'
//                 )
//                 ->value('total') ?? 0
//         );
//     }

//     /**
//      * Bénéfice total.
//      */
//     public function getProfitProperty(): float
//     {
//         return (float) $this->getItemsQuery()->sum('profit');
//     }

//     /**
//      * Nombre de ventes.
//      */
//     public function getSalesCountProperty(): int
//     {
//         return $this->getSalesQuery()->count();
//     }

//     /**
//      * Nombre d'articles vendus.
//      */
//     public function getItemsQuantityProperty(): int
//     {
//         return (int) $this->getItemsQuery()->sum('quantity');
//     }

//     /**
//      * Bénéfice moyen par vente.
//      */
//     public function getAverageProfitProperty(): float
//     {
//         if ($this->salesCount <= 0) {
//             return 0;
//         }

//         return $this->profit / $this->salesCount;
//     }

//     /**
//      * Vente moyenne.
//      */
//     public function getAverageSaleProperty(): float
//     {
//         if ($this->salesCount <= 0) {
//             return 0;
//         }

//         return $this->revenue / $this->salesCount;
//     }

//     /**
//      * Marge globale.
//      */
//     public function getMarginProperty(): float
//     {
//         if ($this->revenue <= 0) {
//             return 0;
//         }

//         return ($this->profit / $this->revenue) * 100;
//     }

//     /**
//      * Export Excel.
//      */
//     public function exportExcel()
//     {
//         return Excel::download(
//             new ProfitReportExport($this->period),
//             'rapport-benefices-' . now()->format('Y-m-d') . '.xlsx'
//         );
//     }

//     /**
//      * Export PDF.
//      */
//     public function exportPdf()
//     {
//         $items = $this->getItemsQuery()
//             ->with([
//                 'sale.customer',
//                 'productVariant.product',
//                 'productVariant.size',
//                 'productVariant.color',
//             ])
//             ->latest('id')
//             ->get();

//         $pdf = Pdf::loadView(
//             'exports.profit-report',
//             [
//                 'items' => $items,
//                 'period' => $this->periodLabel,
//                 'revenue' => $this->revenue,
//                 'cost' => $this->cost,
//                 'profit' => $this->profit,
//                 'margin' => $this->margin,
//                 'salesCount' => $this->salesCount,
//                 'itemsQuantity' => $this->itemsQuantity,
//                 'averageSale' => $this->averageSale,
//                 'averageProfit' => $this->averageProfit,
//             ]
//         );

//         return $pdf
//             ->setPaper('a4', 'landscape')
//             ->download(
//                 'rapport-benefices-' .
//                 now()->format('Y-m-d') .
//                 '.pdf'
//             );
//     }
// }








namespace App\Filament\Pages;

use App\Enums\SaleStatus;
use App\Exports\ProfitReportExport;
use App\Models\Sale;
use App\Models\SaleItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ProfitReport extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    // protected static ?string $navigationGroup = 'Rapports';
protected static string|\UnitEnum|null $navigationGroup = 'Rapports';

    protected static ?string $navigationLabel = 'Rapport des bénéfices';

    protected static ?string $title = 'Rapport des bénéfices';

   //  protected static ?string $navigationIcon = 'heroicon-o-banknotes';
 protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected string $view = 'filament.pages.profit-report';

    public string $period = 'month';

    /*
    |--------------------------------------------------------------------------
    | Période
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

    // public function updatedPeriod(): void
    // {
    //     $this->resetTablePage();
    // }

    /*
    |--------------------------------------------------------------------------
    | Requête de base
    |--------------------------------------------------------------------------
    */

    // public function getItemsQuery(): Builder
    // {
    //     return SaleItem::query()
    //         ->with([
    //             'sale.customer',
    //             'sale.user',
    //             'productVariant.product',
    //             'productVariant.size',
    //             'productVariant.color',
    //         ])
    //         ->whereHas('sale', function (Builder $query) {
    //             $query->where(
    //                 'status',
    //                 SaleStatus::COMPLETED->value
    //             );

    //             match ($this->period) {
    //                 'today' => $query->whereDate(
    //                     'sale_date',
    //                     today()
    //                 ),

    //                 'week' => $query->whereBetween('sale_date', [
    //                     now()->startOfWeek(),
    //                     now()->endOfWeek(),
    //                 ]),

    //                 'month' => $query->whereBetween('sale_date', [
    //                     now()->startOfMonth(),
    //                     now()->endOfMonth(),
    //                 ]),

    //                 'year' => $query->whereBetween('sale_date', [
    //                     now()->startOfYear(),
    //                     now()->endOfYear(),
    //                 ]),

    //                 default => null,
    //             };
    //         });
    // }


    public function getItemsQuery(): Builder
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
        });

    return match ($this->period) {
        'today' => $query->whereHas(
            'sale',
            fn (Builder $saleQuery) =>
                $saleQuery->whereDate('sale_date', today())
        ),

        'week' => $query->whereHas(
            'sale',
            fn (Builder $saleQuery) =>
                $saleQuery->whereBetween('sale_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ])
        ),

        'month' => $query->whereHas(
            'sale',
            fn (Builder $saleQuery) =>
                $saleQuery->whereBetween('sale_date', [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ])
        ),

        'year' => $query->whereHas(
            'sale',
            fn (Builder $saleQuery) =>
                $saleQuery->whereBetween('sale_date', [
                    now()->startOfYear(),
                    now()->endOfYear(),
                ])
        ),

        'all' => $query,

        default => $query->whereHas(
            'sale',
            fn (Builder $saleQuery) =>
                $saleQuery->whereBetween('sale_date', [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ])
        ),
    };
}

    /*
    |--------------------------------------------------------------------------
    | Table Filament
    |--------------------------------------------------------------------------
    */

    public function table(Table $table): Table
    {
        return $table
            // ->query($this->getItemsQuery())
            ->query(fn (): Builder => $this->getItemsQuery())

            ->columns([
                TextColumn::make('sale.reference')
                    ->label('Référence')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('sale.sale_date')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('sale.customer.name')
                    ->label('Client')
                    ->formatStateUsing(
                        fn ($state) => $state ?: 'Client comptant'
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('productVariant.product.name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->description(
                        fn (SaleItem $record) =>
                            'SKU : ' . $record->productVariant->sku
                    ),

                TextColumn::make('quantity')
                    ->label('Qté')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('unit_price')
                    ->label('Prix vente')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->sortable(),

                TextColumn::make('cost_price')
                    ->label('Prix achat')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Chiffre d’affaires')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->sortable(),

                TextColumn::make('profit')
                    ->label('Bénéfice')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->weight('bold')
                    ->color(
                        fn ($state) => $state > 0
                            ? 'success'
                            : ($state < 0 ? 'danger' : 'gray')
                    )
                    ->sortable(),
            ])

            ->filters([
                /*
                |--------------------------------------------------------------------------
                | Type de bénéfice
                |--------------------------------------------------------------------------
                */

                SelectFilter::make('profit_type')
                    ->label('Type de bénéfice')
                    ->options([
                        'positive' => 'Bénéfice positif',
                        'zero' => 'Bénéfice nul',
                        'negative' => 'Perte',
                    ])
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            return match ($data['value'] ?? null) {
                                'positive' => $query->where(
                                    'profit',
                                    '>',
                                    0
                                ),

                                'zero' => $query->where(
                                    'profit',
                                    '=',
                                    0
                                ),

                                'negative' => $query->where(
                                    'profit',
                                    '<',
                                    0
                                ),

                                default => $query,
                            };
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | Produit
                |--------------------------------------------------------------------------
                */

                SelectFilter::make('product')
                    ->label('Produit')
                    ->relationship(
                        'productVariant.product',
                        'name'
                    )
                    ->searchable()
                    ->preload(),

                /*
                |--------------------------------------------------------------------------
                | Période personnalisée
                |--------------------------------------------------------------------------
                */

                Filter::make('custom_date')
                    ->label('Période personnalisée')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Du'),

                        DatePicker::make('until')
                            ->label("Jusqu'au"),
                    ])
                    ->columns(2)
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            return $query->whereHas(
                                'sale',
                                function (
                                    Builder $query
                                ) use ($data) {
                                    $query
                                        ->when(
                                            $data['from'] ?? null,
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
                                            $data['until'] ?? null,
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
                    ),
            ])

            ->defaultSort(
                'sale.sale_date',
                'desc'
            )

            ->searchPlaceholder(
                'Rechercher une vente, un client ou un produit...'
            )

            ->paginated([
                10,
                25,
                50,
                100,
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Requête réellement filtrée
    |--------------------------------------------------------------------------
    |
    | C'est cette requête qui devient notre source de vérité.
    |
    */

    public function getFilteredItemsQuery(): Builder
    {
        return $this->getFilteredTableQuery();
    }

    /*
    |--------------------------------------------------------------------------
    | Statistiques
    |--------------------------------------------------------------------------
    */

    public function getRevenueProperty(): float
    {
        return (float) $this
            ->getFilteredItemsQuery()
            ->sum('total');
    }

    public function getCostProperty(): float
    {
        return (float) (
            $this
                ->getFilteredItemsQuery()
                ->selectRaw(
                    'COALESCE(SUM(cost_price * quantity), 0) as total'
                )
                ->value('total') ?? 0
        );
    }

    public function getProfitProperty(): float
    {
        return (float) $this
            ->getFilteredItemsQuery()
            ->sum('profit');
    }

    // public function getSalesCountProperty(): int
    // {
    //     return (int) $this
    //         ->getFilteredItemsQuery()
    //         ->distinct('sale_id')
    //         ->count('sale_id');
    // }

    public function getSalesCountProperty(): int
{
    return (int) $this
        ->getFilteredItemsQuery()
        ->select('sale_id')
        ->distinct()
        ->count();
}

    public function getItemsQuantityProperty(): int
    {
        return (int) $this
            ->getFilteredItemsQuery()
            ->sum('quantity');
    }

    public function getProductsCountProperty(): int
{
    return (int) $this
        ->getFilteredItemsQuery()
        ->distinct('product_variant_id')
        ->count('product_variant_id');
}

    public function getAverageProfitProperty(): float
    {
        if ($this->salesCount <= 0) {
            return 0;
        }

        return $this->profit / $this->salesCount;
    }

    public function getAverageSaleProperty(): float
    {
        if ($this->salesCount <= 0) {
            return 0;
        }

        return $this->revenue / $this->salesCount;
    }

    public function getMarginProperty(): float
    {
        if ($this->revenue <= 0) {
            return 0;
        }

        return ($this->profit / $this->revenue) * 100;
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
            new ProfitReportExport(
                $this->period,
                $this->tableFilters,
                $this->tableSearch
            ),
            'rapport-benefices-' .
            now()->format('Y-m-d') .
            '.xlsx'
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

        $items = $this
            ->getFilteredItemsQuery()
            ->with([
                'sale.customer',
                'sale.user',
                'productVariant.product',
                'productVariant.size',
                'productVariant.color',
            ])
            ->latest('id')
            ->get();

        $pdf = Pdf::loadView(
            'exports.profit-report',
            [
                'items' => $items,
                'period' => $this->periodLabel,
                'revenue' => $this->revenue,
                'cost' => $this->cost,
                'profit' => $this->profit,
                'margin' => $this->margin,
                'salesCount' => $this->salesCount,
                'itemsQuantity' => $this->itemsQuantity,
                'averageSale' => $this->averageSale,
                'averageProfit' => $this->averageProfit,
            ]
        );

        return $pdf
            ->setPaper('a4', 'landscape')
            ->download(
                'rapport-benefices-' .
                now()->format('Y-m-d') .
                '.pdf'
            );
    }




public static function canAccess(): bool
{
    return auth()->user()?->can('reports.view') ?? false;
}
}