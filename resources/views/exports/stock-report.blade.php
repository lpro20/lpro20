<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Rapport du stock</title>

    <style>

        @page {
            margin: 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1f2937;
        }

        h1 {
            margin: 0;
            font-size: 20px;
        }

        .subtitle {
            color: #6b7280;
            margin-top: 5px;
        }

        .header {
            margin-bottom: 20px;
        }

        .summary {
            width: 100%;
            margin-bottom: 20px;
        }

        .summary td {
            width: 25%;
            padding: 10px;
            border: 1px solid #e5e7eb;
        }

        .label {
            color: #6b7280;
            font-size: 8px;
        }

        .value {
            margin-top: 4px;
            font-size: 13px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 7px;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            font-size: 7px;
        }

        td {
            padding: 7px;
            border: 1px solid #e5e7eb;
            font-size: 7px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .footer {
            margin-top: 15px;
            color: #6b7280;
            font-size: 7px;
        }

    </style>

</head>

<body>

    <div class="header">

        <h1>Rapport du stock</h1>

        <div class="subtitle">
            État de l'inventaire
        </div>

        <div class="subtitle">
            Généré le {{ now()->format('d/m/Y à H:i') }}
        </div>

    </div>


    <table class="summary">

        <tr>

            <td>

                <div class="label">
                    Quantité totale
                </div>

                <div class="value">
                    {{ number_format($totalQuantity, 0, ',', ' ') }}
                </div>

            </td>

            <td>

                <div class="label">
                    Valeur du stock
                </div>

                <div class="value">
                    {{ number_format($stockValue, 0, ',', ' ') }} FCFA
                </div>

            </td>

            <td>

                <div class="label">
                    Stock faible
                </div>

                <div class="value">
                    {{ $lowStockCount }}
                </div>

            </td>

            <td>

                <div class="label">
                    Ruptures
                </div>

                <div class="value">
                    {{ $outOfStockCount }}
                </div>

            </td>

        </tr>

    </table>


    <table>

        <thead>

            <tr>

                <th>Produit</th>
                <th>SKU</th>
                <th>Taille</th>
                <th>Couleur</th>
                <th>Stock</th>
                <th>Seuil</th>
                <th>Prix achat</th>
                <th>Prix vente</th>
                <th>Valeur stock</th>
                <th>État</th>

            </tr>

        </thead>


        <tbody>

            @forelse($variants as $variant)

                @php
                    $status = match (true) {
                        $variant->stock_quantity <= 0 => 'Rupture',
                        $variant->stock_quantity <= $variant->alert_threshold => 'Stock faible',
                        default => 'Normal',
                    };
                @endphp

                <tr>

                    <td>
                        {{ $variant->product->name }}
                    </td>

                    <td>
                        {{ $variant->sku }}
                    </td>

                    <td>
                        {{ $variant->size?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $variant->color?->name ?? '-' }}
                    </td>

                    <td class="center">
                        {{ $variant->stock_quantity }}
                    </td>

                    <td class="center">
                        {{ $variant->alert_threshold }}
                    </td>

                    <td class="right">
                        {{ number_format($variant->purchase_price, 0, ',', ' ') }}
                    </td>

                    <td class="right">
                        {{ number_format($variant->selling_price, 0, ',', ' ') }}
                    </td>

                    <td class="right">
                        {{ number_format(
                            $variant->stock_quantity * $variant->purchase_price,
                            0,
                            ',',
                            ' '
                        ) }}
                    </td>

                    <td class="center">
                        {{ $status }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="10" class="center">
                        Aucun produit disponible.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    <div class="footer">

        Variantes :
        {{ $variantsCount }}

        &nbsp; | &nbsp;

        Mouvements sur la période :
        {{ $movementsCount }}

        &nbsp; | &nbsp;

        Valeur potentielle :
        {{ number_format($potentialSalesValue, 0, ',', ' ') }} FCFA

        &nbsp; | &nbsp;

        Bénéfice potentiel :
        {{ number_format($potentialProfit, 0, ',', ' ') }} FCFA

    </div>

</body>

</html>