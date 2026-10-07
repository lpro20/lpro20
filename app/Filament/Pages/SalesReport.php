<?php

// namespace App\Filament\Pages;

// use App\Enums\PaymentMethod;
// use App\Enums\SaleStatus;
// use App\Exports\SalesReportExport;
// use App\Models\Sale;
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

// class SalesReport extends Page implements Tables\Contracts\HasTable
// {
//     use Tables\Concerns\InteractsWithTable;

//     protected static string|\UnitEnum|null $navigationGroup = 'Rapports';

//     protected static ?string $navigationLabel = 'Rapport des ventes';

//     protected static ?string $title = 'Rapport des ventes';

//     protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

//     protected string $view = 'filament.pages.sales-report';

//     public string $period = 'month';

//     /**
//      * Libellé de la période sélectionnée.
//      */
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
//      * Requête principale des ventes.
//      *
//      * Seules les ventes terminées sont prises en compte.
//      */
//     public function getSalesQuery(): Builder
//     {
//         $query = Sale::query()
//             ->where('status', SaleStatus::COMPLETED->value);

//         return match ($this->period) {
//             'today' => $query->whereDate(
//                 'sale_date',
//                 today()
//             ),

//             'week' => $query->whereBetween(
//                 'sale_date',
//                 [
//                     now()->startOfWeek(),
//                     now()->endOfWeek(),
//                 ]
//             ),

//             'month' => $query->whereBetween(
//                 'sale_date',
//                 [
//                     now()->startOfMonth(),
//                     now()->endOfMonth(),
//                 ]
//             ),

//             'year' => $query->whereBetween(
//                 'sale_date',
//                 [
//                     now()->startOfYear(),
//                     now()->endOfYear(),
//                 ]
//             ),

//             'all' => $query,

//             default => $query->whereBetween(
//                 'sale_date',
//                 [
//                     now()->startOfMonth(),
//                     now()->endOfMonth(),
//                 ]
//             ),
//         };
//     }

//     /**
//      * Tableau des ventes.
//      */
//     public function table(Table $table): Table
//     {
//         return $table
//             /*
//              * Important :
//              * on utilise une closure afin que la requête
//              * soit reconstruite lorsque $period change.
//              */
//             ->query(
//                 fn (): Builder => $this->getSalesQuery()
//             )

//             ->columns([
//                 TextColumn::make('reference')
//                     ->label('Référence')
//                     ->searchable()
//                     ->sortable()
//                     ->copyable(),

//                 TextColumn::make('sale_date')
//                     ->label('Date')
//                     ->dateTime('d/m/Y H:i')
//                     ->sortable(),

//                 TextColumn::make('customer.name')
//                     ->label('Client')
//                     ->formatStateUsing(
//                         fn ($state) => $state ?: 'Client comptant'
//                     )
//                     ->searchable()
//                     ->sortable(),

//                 TextColumn::make('user.name')
//                     ->label('Vendeur')
//                     ->searchable()
//                     ->sortable(),

//                 TextColumn::make('payment_method')
//                     ->label('Paiement')
//                     ->badge()
//                     ->formatStateUsing(
//                         fn ($state) => $state instanceof PaymentMethod
//                             ? $state->label()
//                             : (string) $state
//                     )
//                     ->color('gray'),

//                 TextColumn::make('subtotal')
//                     ->label('Sous-total')
//                     ->numeric(decimalPlaces: 0)
//                     ->suffix(' FCFA')
//                     ->sortable(),

//                 TextColumn::make('discount')
//                     ->label('Remise')
//                     ->numeric(decimalPlaces: 0)
//                     ->suffix(' FCFA')
//                     ->color(
//                         fn ($state) => $state > 0
//                             ? 'warning'
//                             : 'gray'
//                     )
//                     ->sortable(),

//                 TextColumn::make('total')
//                     ->label('Total')
//                     ->numeric(decimalPlaces: 0)
//                     ->suffix(' FCFA')
//                     ->weight('bold')
//                     ->sortable(),

//                 TextColumn::make('amount_paid')
//                     ->label('Payé')
//                     ->numeric(decimalPlaces: 0)
//                     ->suffix(' FCFA')
//                     ->toggleable(
//                         isToggledHiddenByDefault: true
//                     ),

//                 TextColumn::make('change_amount')
//                     ->label('Monnaie')
//                     ->numeric(decimalPlaces: 0)
//                     ->suffix(' FCFA')
//                     ->toggleable(
//                         isToggledHiddenByDefault: true
//                     ),
//             ])

//             ->filters([
//                 SelectFilter::make('payment_method')
//                     ->label('Mode de paiement')
//                     ->options([
//                         'cash' => 'Espèces',
//                         'mobile_money' => 'Mobile Money',
//                         'card' => 'Carte bancaire',
//                         'bank_transfer' => 'Virement',
//                     ])
//                     ->multiple(),

//                 SelectFilter::make('user_id')
//                     ->label('Vendeur')
//                     ->relationship('user', 'name')
//                     ->searchable()
//                     ->preload(),

//                 Filter::make('sale_date')
//                     ->label('Période personnalisée')
//                     ->schema([
//                         DatePicker::make('from')
//                             ->label('Du'),

//                         DatePicker::make('until')
//                             ->label("Jusqu'au"),
//                     ])
//                     ->columns(2)
//                     ->query(
//                         function (
//                             Builder $query,
//                             array $data
//                         ): Builder {
//                             return $query
//                                 ->when(
//                                     $data['from'] ?? null,
//                                     fn (
//                                         Builder $query,
//                                         $date
//                                     ) => $query->whereDate(
//                                         'sale_date',
//                                         '>=',
//                                         $date
//                                     )
//                                 )
//                                 ->when(
//                                     $data['until'] ?? null,
//                                     fn (
//                                         Builder $query,
//                                         $date
//                                     ) => $query->whereDate(
//                                         'sale_date',
//                                         '<=',
//                                         $date
//                                     )
//                                 );
//                         }
//                     ),
//             ])

//             ->defaultSort(
//                 'sale_date',
//                 'desc'
//             )

//             ->searchPlaceholder(
//                 'Rechercher une vente...'
//             )

//             ->paginated([
//                 10,
//                 25,
//                 50,
//                 100,
//             ]);
//     }

//     /**
//      * Chiffre d'affaires de la période.
//      */
//     public function getTotalSalesProperty(): float
//     {
//         return (float) $this
//             ->getSalesQuery()
//             ->sum('total');
//     }

//     /**
//      * Nombre de ventes.
//      */
//     public function getSalesCountProperty(): int
//     {
//         return (int) $this
//             ->getSalesQuery()
//             ->count();
//     }

//     /**
//      * Nombre total d'articles vendus.
//      */
//     public function getItemsQuantityProperty(): int
//     {
//         return (int) $this
//             ->getSalesQuery()
//             ->withSum('items', 'quantity')
//             ->get()
//             ->sum('items_sum_quantity');
//     }

//     /**
//      * Panier moyen.
//      */
//     public function getAverageSaleProperty(): float
//     {
//         if ($this->salesCount <= 0) {
//             return 0;
//         }

//         return $this->totalSales / $this->salesCount;
//     }

//     /**
//      * Export Excel.
//      */
//     public function exportExcel()
//     {
//         return Excel::download(
//             new SalesReportExport($this->period),
//             'rapport-ventes-' . now()->format('Y-m-d') . '.xlsx'
//         );
//     }

//     /**
//      * Export PDF.
//      */
//     public function exportPdf()
//     {
//         $sales = $this
//             ->getSalesQuery()
//             ->with([
//                 'customer',
//                 'user',
//                 'items.productVariant.product',
//             ])
//             ->latest('sale_date')
//             ->get();

//         $pdf = Pdf::loadView(
//             'exports.sales-report',
//             [
//                 'sales' => $sales,
//                 'period' => $this->periodLabel,
//                 'totalSales' => $this->totalSales,
//                 'salesCount' => $this->salesCount,
//                 'itemsQuantity' => $this->itemsQuantity,
//                 'averageSale' => $this->averageSale,
//             ]
//         );

//         return $pdf
//             ->setPaper('a4', 'landscape')
//             ->download(
//                 'rapport-ventes-' . now()->format('Y-m-d') . '.pdf'
//             );
//     }
// }










namespace App\Filament\Pages;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Exports\SalesReportExport;
use App\Models\Sale;
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

class SalesReport extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'Rapports';

    protected static ?string $navigationLabel = 'Rapport des ventes';

    protected static ?string $title = 'Rapport des ventes';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.pages.sales-report';

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

    /*
    |--------------------------------------------------------------------------
    | Requête principale
    |--------------------------------------------------------------------------
    */

    public function getSalesQuery(): Builder
    {
        $query = Sale::query()
            ->where(
                'status',
                SaleStatus::COMPLETED->value
            );

        return match ($this->period) {
            'today' => $query->whereDate(
                'sale_date',
                today()
            ),

            'week' => $query->whereBetween(
                'sale_date',
                [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]
            ),

            'month' => $query->whereBetween(
                'sale_date',
                [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ]
            ),

            'year' => $query->whereBetween(
                'sale_date',
                [
                    now()->startOfYear(),
                    now()->endOfYear(),
                ]
            ),

            'all' => $query,

            default => $query->whereBetween(
                'sale_date',
                [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ]
            ),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Requête avec filtres Filament
    |--------------------------------------------------------------------------
    */

    public function getFilteredSalesQuery(): Builder
    {
        return $this->getFilteredTableQuery();
    }

    /*
    |--------------------------------------------------------------------------
    | Tableau
    |--------------------------------------------------------------------------
    */

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => $this->getSalesQuery()
            )

            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('sale_date')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Client')
                    ->formatStateUsing(
                        fn ($state) => $state ?: 'Client comptant'
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Vendeur')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Paiement')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => $state instanceof PaymentMethod
                            ? $state->label()
                            : (string) $state
                    )
                    ->color('gray'),

                TextColumn::make('subtotal')
                    ->label('Sous-total')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->sortable(),

                TextColumn::make('discount')
                    ->label('Remise')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->color(
                        fn ($state) => $state > 0
                            ? 'warning'
                            : 'gray'
                    )
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('amount_paid')
                    ->label('Payé')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

                TextColumn::make('change_amount')
                    ->label('Monnaie')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])

            ->filters([
                SelectFilter::make('payment_method')
                    ->label('Mode de paiement')
                    ->options([
                        'cash' => 'Espèces',
                        'mobile_money' => 'Mobile Money',
                        'card' => 'Carte bancaire',
                        'bank_transfer' => 'Virement',
                    ])
                    ->multiple(),

                SelectFilter::make('user_id')
                    ->label('Vendeur')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('sale_date')
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
                            return $query
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
                    ),
            ])

            ->defaultSort(
                'sale_date',
                'desc'
            )

            ->searchPlaceholder(
                'Rechercher une vente, un client ou une référence...'
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
    | Statistiques
    |--------------------------------------------------------------------------
    */

    /**
     * Chiffre d'affaires filtré.
     */
    public function getTotalSalesProperty(): float
    {
        return (float) $this
            ->getFilteredSalesQuery()
            ->sum('total');
    }

    /**
     * Nombre de ventes filtrées.
     */
    public function getSalesCountProperty(): int
    {
        return (int) $this
            ->getFilteredSalesQuery()
            ->count();
    }

    /**
     * Nombre d'articles vendus.
     */
    public function getItemsQuantityProperty(): int
    {
        return (int) $this
            ->getFilteredSalesQuery()
            ->withSum('items', 'quantity')
            ->get()
            ->sum('items_sum_quantity');
    }

    /**
     * Panier moyen.
     */
    public function getAverageSaleProperty(): float
    {
        if ($this->salesCount <= 0) {
            return 0;
        }

        return $this->totalSales / $this->salesCount;
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
            new SalesReportExport(
                $this->getFilteredSalesQuery()
            ),
            'rapport-ventes-' . now()->format('Y-m-d') . '.xlsx'
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

        $sales = $this
            ->getFilteredSalesQuery()
            ->with([
                'customer',
                'user',
                'items.productVariant.product',
            ])
            ->latest('sale_date')
            ->get();

        $pdf = Pdf::loadView(
            'exports.sales-report',
            [
                'sales' => $sales,
                'period' => $this->periodLabel,
                'totalSales' => $this->totalSales,
                'salesCount' => $this->salesCount,
                'itemsQuantity' => $this->itemsQuantity,
                'averageSale' => $this->averageSale,
            ]
        );

        return $pdf
            ->setPaper('a4', 'landscape')
            ->download(
                'rapport-ventes-' . now()->format('Y-m-d') . '.pdf'
            );
    }





public static function canAccess(): bool
{
    return auth()->user()?->can('reports.view') ?? false;
}
}