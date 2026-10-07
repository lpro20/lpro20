<x-filament-panels::page>

    {{-- En-tête --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h2 class="text-xl font-semibold tracking-tight">
                État du stock
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Vue globale de votre inventaire et de sa valeur.
            </p>
        </div>

        <div class="flex items-center gap-2">
        @can('reports.export')
            <x-filament::button
                wire:click="exportExcel"
                icon="heroicon-o-arrow-down-tray"
                color="success"
            >
                Excel
            </x-filament::button>

            <x-filament::button
                wire:click="exportPdf"
                icon="heroicon-o-document-arrow-down"
                color="gray"
            >
                PDF
            </x-filament::button>
        @endcan
        </div>

    </div>


    {{-- Filtre --}}
    <x-filament::section
        heading="Période d'analyse"
        description="La période concerne les mouvements de stock. L'état du stock correspond à la situation actuelle."
    >

        <div class="flex flex-wrap gap-2">

            @foreach([
                'today' => "Aujourd'hui",
                'week' => 'Cette semaine',
                'month' => 'Ce mois',
                'year' => 'Cette année',
                'all' => 'Tout',
            ] as $value => $label)

                <x-filament::button
                    wire:click="$set('period', '{{ $value }}')"
                    :color="$period === $value ? 'primary' : 'gray'"
                    size="sm"
                >
                    {{ $label }}
                </x-filament::button>

            @endforeach

        </div>

    </x-filament::section>


    {{-- Statistiques --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-filament::section>

            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Quantité en stock
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ number_format($this->totalQuantity, 0, ',', ' ') }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        {{ $this->variantsCount }} variante(s)
                    </p>

                </div>

                <x-filament::icon
                    icon="heroicon-o-cube"
                    class="h-6 w-6 text-primary-600"
                />

            </div>

        </x-filament::section>


        <x-filament::section>

            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Valeur du stock
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ number_format($this->stockValue, 0, ',', ' ') }}
                        FCFA
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Au prix d'achat
                    </p>

                </div>

                <x-filament::icon
                    icon="heroicon-o-banknotes"
                    class="h-6 w-6 text-primary-600"
                />

            </div>

        </x-filament::section>


        <x-filament::section>

            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Sous le seuil
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ $this->lowStockCount }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Variante(s) à surveiller
                    </p>

                </div>

                <x-filament::icon
                    icon="heroicon-o-exclamation-triangle"
                    class="h-6 w-6 text-warning-600"
                />

            </div>

        </x-filament::section>


        <x-filament::section>

            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Ruptures
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ $this->outOfStockCount }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Variante(s) sans stock
                    </p>

                </div>

                <x-filament::icon
                    icon="heroicon-o-x-circle"
                    class="h-6 w-6 text-danger-600"
                />

            </div>

        </x-filament::section>

    </div>


    {{-- Informations financières --}}
    <x-filament::section
        heading="Valorisation du stock"
        description="Estimation de la valeur actuelle de l'inventaire."
    >

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Valeur d'achat
                </p>

                <p class="mt-1 text-lg font-semibold">
                    {{ number_format($this->stockValue, 0, ',', ' ') }}
                    FCFA
                </p>

            </div>


            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Valeur potentielle de vente
                </p>

                <p class="mt-1 text-lg font-semibold">
                    {{ number_format($this->potentialSalesValue, 0, ',', ' ') }}
                    FCFA
                </p>

            </div>


            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Bénéfice potentiel
                </p>

                <p class="mt-1 text-lg font-semibold text-success-600">
                    {{ number_format($this->potentialProfit, 0, ',', ' ') }}
                    FCFA
                </p>

            </div>

        </div>

    </x-filament::section>


    {{-- Alertes --}}
    @if($this->lowStockCount > 0 || $this->outOfStockCount > 0)

        <x-filament::section
            heading="Alertes de stock"
            description="Les variantes suivantes nécessitent votre attention."
        >

            <div class="space-y-3">

                @if($this->outOfStockCount > 0)

                    <div class="flex items-center gap-3 rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 dark:border-danger-800 dark:bg-danger-950/30">

                        <x-filament::icon
                            icon="heroicon-o-x-circle"
                            class="h-5 w-5 text-danger-600"
                        />

                        <div class="text-sm">

                            <span class="font-medium text-danger-700 dark:text-danger-400">
                                {{ $this->outOfStockCount }} variante(s)
                            </span>

                            sont actuellement en rupture de stock.

                        </div>

                    </div>

                @endif


                @if($this->lowStockCount > 0)

                    <div class="flex items-center gap-3 rounded-lg border border-warning-200 bg-warning-50 px-4 py-3 dark:border-warning-800 dark:bg-warning-950/30">

                        <x-filament::icon
                            icon="heroicon-o-exclamation-triangle"
                            class="h-5 w-5 text-warning-600"
                        />

                        <div class="text-sm">

                            <span class="font-medium text-warning-700 dark:text-warning-400">
                                {{ $this->lowStockCount }} variante(s)
                            </span>

                            sont sous leur seuil d'alerte.

                        </div>

                    </div>

                @endif

            </div>

        </x-filament::section>

    @endif


    {{-- Tableau principal --}}
    <x-filament::section
        heading="Inventaire actuel"
        description="Détail des variantes actuellement disponibles dans le stock."
    >

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead>

                    <tr class="border-b border-gray-200 dark:border-gray-700">

                        <th class="px-4 py-3 font-medium text-gray-500">
                            Produit
                        </th>

                        <th class="px-4 py-3 font-medium text-gray-500">
                            SKU
                        </th>

                        <th class="px-4 py-3 font-medium text-gray-500">
                            Variante
                        </th>

                        <th class="px-4 py-3 text-center font-medium text-gray-500">
                            Stock
                        </th>

                        <th class="px-4 py-3 text-center font-medium text-gray-500">
                            Seuil
                        </th>

                        <th class="px-4 py-3 text-right font-medium text-gray-500">
                            Prix achat
                        </th>

                        <th class="px-4 py-3 text-right font-medium text-gray-500">
                            Valeur
                        </th>

                        <th class="px-4 py-3 text-center font-medium text-gray-500">
                            État
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">

                    @forelse(
                        $this->getStockQuery()
                            ->orderBy('stock_quantity')
                            ->get()
                        as $variant
                    )

                        @php
                            $isOut = $variant->stock_quantity <= 0;
                            $isLow = !$isOut &&
                                $variant->stock_quantity <= $variant->alert_threshold;
                        @endphp

                        <tr class="transition hover:bg-gray-50 dark:hover:bg-white/5">

                            <td class="px-4 py-3">

                                <div class="font-medium">
                                    {{ $variant->product->name }}
                                </div>

                            </td>


                            <td class="px-4 py-3 text-gray-500">
                                {{ $variant->sku }}
                            </td>


                            <td class="px-4 py-3 text-gray-500">

                                @if($variant->size)
                                    {{ $variant->size->name }}
                                @endif

                                @if($variant->color)
                                    · {{ $variant->color->name }}
                                @endif

                                @if(!$variant->size && !$variant->color)
                                    -
                                @endif

                            </td>


                            <td class="px-4 py-3 text-center font-semibold">
                                {{ $variant->stock_quantity }}
                            </td>


                            <td class="px-4 py-3 text-center text-gray-500">
                                {{ $variant->alert_threshold }}
                            </td>


                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                {{ number_format($variant->purchase_price, 0, ',', ' ') }}
                                FCFA
                            </td>


                            <td class="px-4 py-3 text-right whitespace-nowrap font-medium">
                                {{ number_format(
                                    $variant->stock_quantity * $variant->purchase_price,
                                    0,
                                    ',',
                                    ' '
                                ) }}
                                FCFA
                            </td>


                            <td class="px-4 py-3 text-center">

                                @if($isOut)

                                    <x-filament::badge color="danger">
                                        Rupture
                                    </x-filament::badge>

                                @elseif($isLow)

                                    <x-filament::badge color="warning">
                                        Stock faible
                                    </x-filament::badge>

                                @else

                                    <x-filament::badge color="success">
                                        Normal
                                    </x-filament::badge>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="px-4 py-12 text-center text-gray-500"
                            >
                                Aucun produit en stock.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-filament::section>

</x-filament-panels::page>