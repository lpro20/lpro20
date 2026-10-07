<x-filament-panels::page>

    {{-- En-tête --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h2 class="text-xl font-semibold tracking-tight">
                Rapport des bénéfices
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Analyse du chiffre d'affaires, des coûts et de la rentabilité.
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


    {{-- Période --}}
    <x-filament::section
        heading="Période d'analyse"
        description="Sélectionnez la période à analyser."
    >

        <div class="flex flex-wrap gap-2">

            @foreach([
                'today' => "Aujourd'hui",
                'week' => 'Cette semaine',
                'month' => 'Ce mois',
                'year' => 'Cette année',
                'all' => 'Toutes les périodes',
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


    {{-- Statistiques principales --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Chiffre d'affaires --}}
        <x-filament::section>

            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Chiffre d'affaires
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ number_format($this->revenue, 0, ',', ' ') }}
                        FCFA
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Total des ventes
                    </p>

                </div>

                <x-filament::icon
                    icon="heroicon-o-banknotes"
                    class="h-6 w-6 text-primary-600"
                />

            </div>

        </x-filament::section>


        {{-- Coût --}}
        <x-filament::section>

            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Coût des marchandises
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ number_format($this->cost, 0, ',', ' ') }}
                        FCFA
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Coût d'achat des articles
                    </p>

                </div>

                <x-filament::icon
                    icon="heroicon-o-shopping-bag"
                    class="h-6 w-6 text-primary-600"
                />

            </div>

        </x-filament::section>


        {{-- Bénéfice --}}
        <x-filament::section>

            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bénéfice
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-success-600">
                        {{ number_format($this->profit, 0, ',', ' ') }}
                        FCFA
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Bénéfice brut
                    </p>

                </div>

                <x-filament::icon
                    icon="heroicon-o-arrow-trending-up"
                    class="h-6 w-6 text-success-600"
                />

            </div>

        </x-filament::section>


        {{-- Marge --}}
        <x-filament::section>

            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Marge
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ number_format($this->margin, 2, ',', ' ') }} %
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Bénéfice / chiffre d'affaires
                    </p>

                </div>

                <x-filament::icon
                    icon="heroicon-o-chart-pie"
                    class="h-6 w-6 text-primary-600"
                />

            </div>

        </x-filament::section>

    </div>


    {{-- Résumé --}}
    <x-filament::section
        heading="Résumé de la rentabilité"
        description="Indicateurs complémentaires pour la période sélectionnée."
    >

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">

            {{-- Ventes --}}
            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Ventes
                </p>

                <p class="mt-1 text-lg font-semibold">
                    {{ $this->salesCount }}
                </p>

            </div>


            {{-- Articles --}}
            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Articles vendus
                </p>

                <p class="mt-1 text-lg font-semibold">
                    {{ number_format($this->itemsQuantity, 0, ',', ' ') }}
                </p>

            </div>


            {{-- Produits différents --}}
            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Produits différents
                </p>

                <p class="mt-1 text-lg font-semibold">
                    {{ $this->productsCount }}
                </p>

            </div>


            {{-- Vente moyenne --}}
            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Vente moyenne
                </p>

                <p class="mt-1 text-lg font-semibold">
                    {{ number_format($this->averageSale, 0, ',', ' ') }}
                    FCFA
                </p>

            </div>


            {{-- Bénéfice moyen --}}
            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Bénéfice moyen
                </p>

                <p class="mt-1 text-lg font-semibold text-success-600">
                    {{ number_format($this->averageProfit, 0, ',', ' ') }}
                    FCFA
                </p>

            </div>

        </div>

    </x-filament::section>


    {{-- Tableau --}}
    <x-filament::section
        heading="Détail des bénéfices"
        description="Analyse détaillée des ventes et de leur rentabilité."
    >

        {{ $this->table }}

    </x-filament::section>

</x-filament-panels::page>