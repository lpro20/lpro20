<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Rapport des ventes</title>

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

        .footer {
            margin-top: 20px;
            font-size: 8px;
            color: #6b7280;
        }

    </style>

</head>

<body>

    <div class="header">

        <h1>Rapport des ventes</h1>

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
                    {{ number_format($totalSales, 0, ',', ' ') }} FCFA
                </div>
            </td>

            <td>
                <div class="label">
                    Nombre de ventes
                </div>

                <div class="value">
                    {{ $salesCount }}
                </div>
            </td>

            <td>
                <div class="label">
                    Articles vendus
                </div>

                <div class="value">
                    {{ $itemsQuantity }}
                </div>
            </td>

            <td>
                <div class="label">
                    Vente moyenne
                </div>

                <div class="value">
                    {{ number_format($averageSale, 0, ',', ' ') }} FCFA
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

                <th>Vendeur</th>

                <th>Paiement</th>

                <th class="right">
                    Sous-total
                </th>

                <th class="right">
                    Remise
                </th>

                <th class="right">
                    Total
                </th>

                <th class="right">
                    Payé
                </th>

                <th class="right">
                    Monnaie
                </th>

            </tr>

        </thead>


        <tbody>

            @forelse($sales as $sale)

                <tr>

                    <td>
                        {{ $sale->reference }}
                    </td>

                    <td>
                        {{ $sale->sale_date?->format('d/m/Y H:i') }}
                    </td>

                    <td>
                        {{ $sale->customer?->name ?? 'Client comptant' }}
                    </td>

                    <td>
                        {{ $sale->user?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $sale->payment_method }}
                    </td>

                    <td class="right">
                        {{ number_format($sale->subtotal, 0, ',', ' ') }}
                    </td>

                    <td class="right">
                        {{ number_format($sale->discount, 0, ',', ' ') }}
                    </td>

                    <td class="right">
                        {{ number_format($sale->total, 0, ',', ' ') }}
                    </td>

                    <td class="right">
                        {{ number_format($sale->amount_paid, 0, ',', ' ') }}
                    </td>

                    <td class="right">
                        {{ number_format($sale->change_amount, 0, ',', ' ') }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="10" class="center">
                        Aucune vente disponible.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    <div class="footer">

        Rapport généré automatiquement par le système de gestion de stock.

    </div>

</body>

</html>