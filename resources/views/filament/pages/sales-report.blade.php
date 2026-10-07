<x-filament-panels::page>

    {{-- =========================================================
        EN-TÊTE
    ========================================================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h2 class="text-xl font-semibold tracking-tight">
                Rapport des ventes
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Analyse du chiffre d'affaires et de l'activité commerciale.
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


    {{-- =========================================================
        PÉRIODE
    ========================================================== --}}
    <x-filament::section
        heading="Période d'analyse"
        description="Sélectionnez la période sur laquelle analyser les ventes."
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


    {{-- =========================================================
        STATISTIQUES PRINCIPALES
    ========================================================== --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Chiffre d'affaires --}}
        <x-filament::section>

            <div class="flex items-start justify-between gap-4">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Chiffre d'affaires
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ number_format($this->totalSales, 0, ',', ' ') }}
                        FCFA
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Ventes terminées
                    </p>
                </div>

                <x-filament::icon
                    icon="heroicon-o-banknotes"
                    class="h-6 w-6 text-primary-600"
                />

            </div>

        </x-filament::section>


        {{-- Nombre de ventes --}}
        <x-filament::section>

            <div class="flex items-start justify-between gap-4">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Nombre de ventes
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ number_format($this->salesCount, 0, ',', ' ') }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Transactions terminées
                    </p>
                </div>

                <x-filament::icon
                    icon="heroicon-o-shopping-cart"
                    class="h-6 w-6 text-primary-600"
                />

            </div>

        </x-filament::section>


        {{-- Articles vendus --}}
        <x-filament::section>

            <div class="flex items-start justify-between gap-4">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Articles vendus
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ number_format($this->itemsQuantity, 0, ',', ' ') }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Quantité totale vendue
                    </p>
                </div>

                <x-filament::icon
                    icon="heroicon-o-cube"
                    class="h-6 w-6 text-primary-600"
                />

            </div>

        </x-filament::section>


        {{-- Vente moyenne --}}
        <x-filament::section>

            <div class="flex items-start justify-between gap-4">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Vente moyenne
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ number_format($this->averageSale, 0, ',', ' ') }}
                        FCFA
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        CA moyen par vente
                    </p>
                </div>

                <x-filament::icon
                    icon="heroicon-o-chart-bar"
                    class="h-6 w-6 text-primary-600"
                />

            </div>

        </x-filament::section>

    </div>


    {{-- =========================================================
        RÉSUMÉ
    ========================================================== --}}
    <x-filament::section
        heading="Résumé des ventes"
        description="Synthèse de l'activité commerciale pour la période sélectionnée."
    >

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Période --}}
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Période sélectionnée
                </p>

                <p class="mt-1 text-lg font-semibold">
                    {{ $this->periodLabel }}
                </p>
            </div>


            {{-- Ventes --}}
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Transactions
                </p>

                <p class="mt-1 text-lg font-semibold">
                    {{ number_format($this->salesCount, 0, ',', ' ') }}
                    vente(s)
                </p>
            </div>


            {{-- Articles --}}
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Articles vendus
                </p>

                <p class="mt-1 text-lg font-semibold">
                    {{ number_format($this->itemsQuantity, 0, ',', ' ') }}
                    article(s)
                </p>
            </div>


            {{-- Panier moyen --}}
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Panier moyen
                </p>

                <p class="mt-1 text-lg font-semibold">
                    {{ number_format($this->averageSale, 0, ',', ' ') }}
                    FCFA
                </p>
            </div>

        </div>

    </x-filament::section>


    {{-- =========================================================
        TABLEAU DES VENTES
    ========================================================== --}}
    <x-filament::section
        heading="Détail des ventes"
        description="Liste des ventes correspondant à la période et aux filtres sélectionnés."
    >

        {{ $this->table }}

    </x-filament::section>

</x-filament-panels::page>