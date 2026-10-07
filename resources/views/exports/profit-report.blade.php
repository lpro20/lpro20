<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Rapport des bénéfices</title>

    <style>

        @page {
            margin: 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        h1 {
            margin: 0;
            font-size: 20px;
        }

        .subtitle {
            margin-top: 5px;
            color: #6b7280;
        }

        .header {
            margin-bottom: 25px;
        }

        .summary {
            width: 100%;
            margin-bottom: 25px;
        }

        .summary td {
            width: 25%;
            padding: 10px;
            border: 1px solid #e5e7eb;
        }

        .label {
            color: #6b7280;
            font-size: 9px;
        }

        .value {
            margin-top: 5px;
            font-size: 14px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 9px 7px;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            font-size: 8px;
            text-align: left;
        }

        td {
            padding: 8px 7px;
            border: 1px solid #e5e7eb;
            font-size: 8px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .profit {
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            font-size: 8px;
            color: #6b7280;
        }

    </style>

</head>

<body>

    <div class="header">

        <h1>Rapport des bénéfices</h1>

        <div class="subtitle">
            Période : {{ $period }}
        </div>

        <div class="subtitle">
            Généré le {{ now()->format('d/m/Y à H:i') }}
        </div>

    </div>


    <table class="summary">

        <tr>

            <td>
                <div class="label">
                    Chiffre d'affaires
                </div>

                <div class="value">
                    {{ number_format($revenue, 0, ',', ' ') }} FCFA
                </div>
            </td>

            <td>
                <div class="label">
                    Coût
                </div>

                <div class="value">
                    {{ number_format($cost, 0, ',', ' ') }} FCFA
                </div>
            </td>

            <td>
                <div class="label">
                    Bénéfice
                </div>

                <div class="value">
                    {{ number_format($profit, 0, ',', ' ') }} FCFA
                </div>
            </td>

            <td>
                <div class="label">
                    Marge
                </div>

                <div class="value">
                    {{ number_format($margin, 2, ',', ' ') }} %
                </div>
            </td>

        </tr>

    </table>


    <table>

        <thead>

            <tr>

                <th>Référence</th>

                <th>Date</th>

                <th>Client</th>

                <th>Produit</th>

                <th class="center">
                    Qté
                </th>

                <th class="right">
                    Prix vente
                </th>

                <th class="right">
                    Prix achat
                </th>

                <th class="right">
                    CA
                </th>

                <th class="right">
                    Coût
                </th>

                <th class="right">
                    Bénéfice
                </th>

            </tr>

        </thead>


        <tbody>

            @forelse($items as $item)

                <tr>

                    <td>
                        {{ $item->sale->reference }}
                    </td>

                    <td>
                        {{ $item->sale->sale_date?->format('d/m/Y H:i') }}
                    </td>

                    <td>
                        {{ $item->sale->customer?->name ?? 'Client comptant' }}
                    </td>

                    <td>
                        {{ $item->productVariant->product->name }}
                    </td>

                    <td class="center">
                        {{ $item->quantity }}
                    </td>

                    <td class="right">
                        {{ number_format($item->unit_price, 0, ',', ' ') }}
                    </td>

                    <td class="right">
                        {{ number_format($item->cost_price, 0, ',', ' ') }}
                    </td>

                    <td class="right">
                        {{ number_format($item->total, 0, ',', ' ') }}
                    </td>

                    <td class="right">
                        {{ number_format(
                            $item->cost_price * $item->quantity,
                            0,
                            ',',
                            ' '
                        ) }}
                    </td>

                    <td class="right profit">
                        {{ number_format($item->profit, 0, ',', ' ') }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="10" class="center">
                        Aucune donnée disponible.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    <div class="footer">

        Nombre de ventes :
        {{ $salesCount }}

        &nbsp;&nbsp;|&nbsp;&nbsp;

        Articles vendus :
        {{ $itemsQuantity }}

        &nbsp;&nbsp;|&nbsp;&nbsp;

        Vente moyenne :
        {{ number_format($averageSale, 0, ',', ' ') }} FCFA

        &nbsp;&nbsp;|&nbsp;&nbsp;

        Bénéfice moyen :
        {{ number_format($averageProfit, 0, ',', ' ') }} FCFA

    </div>

</body>

</html>