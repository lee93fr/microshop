<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
.header { background: #1e293b; color: white; padding: 20px 28px; }
.header h1 { font-size: 18px; font-weight: bold; margin-bottom: 2px; }
.header .ref { font-size: 13px; opacity: 0.8; text-align: right; }
.meta { display: flex; justify-content: space-between; align-items: flex-start; }
.body { padding: 20px 28px; }
.section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin-bottom: 10px; margin-top: 18px; }
table { width: 100%; border-collapse: collapse; font-size: 11px; }
th { background: #f3f4f6; padding: 7px 10px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; font-weight: 600; }
td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.total-row td { font-weight: bold; border-top: 2px solid #1e293b; padding-top: 9px; }
.footer { padding: 12px 28px; background: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center; font-size: 9px; color: #9ca3af; }
.info-grid { display: flex; gap: 24px; font-size: 11px; }
.info-block { flex: 1; }
.info-block p { margin-bottom: 3px; }
</style>
</head>
<body>

<div class="header">
    <div class="meta">
        <div>
            <h1>{{ $shopName }}</h1>
            <p style="opacity:.7; font-size:10px; margin-top:2px;">{{ $shopEmail }}</p>
        </div>
        <div class="ref">
            <p style="font-size:16px; font-weight:bold;">BON DE COMMANDE</p>
            <p style="margin-top:3px;">Réf. {{ $supplierOrder->reference }}</p>
            <p>Date : {{ now()->format('d/m/Y') }}</p>
            <p>Statut : {{ $supplierOrder->status_label }}</p>
        </div>
    </div>
</div>

<div class="body">
    <div class="section-title">Destinataire</div>
    <div class="info-grid">
        <div class="info-block">
            <p><strong>Fournisseur :</strong> {{ $supplierName }}</p>
        </div>
        <div class="info-block">
            <p><strong>Commandes incluses :</strong> {{ count($supplierOrder->order_ids) }}</p>
            <p><strong>Références :</strong> {{ $orders->pluck('reference')->join(', ') }}</p>
        </div>
    </div>

    <div class="section-title">Produits à commander (consolidé)</div>
    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>SKU</th>
                <th class="text-center">Unité</th>
                <th class="text-center">Quantité</th>
                <th class="text-right">Prix achat/unité</th>
                <th class="text-right">Total achat</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($consolidated as $line)
            @php
                $lineTotal = $line['product']->purchase_price * $line['quantity'];
                $grandTotal += $lineTotal;
            @endphp
            <tr>
                <td>{{ $line['product']->name }}</td>
                <td style="color:#9ca3af; font-size:10px;">{{ $line['product']->sku ?? '—' }}</td>
                <td class="text-center">{{ $line['product']->unit }}</td>
                <td class="text-center" style="font-weight:bold;">{{ $line['quantity'] }}</td>
                <td class="text-right">{{ number_format($line['product']->purchase_price, 2, ',', ' ') }} €</td>
                <td class="text-right" style="font-weight:bold;">{{ number_format($lineTotal, 2, ',', ' ') }} €</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">Total commande fournisseur</td>
                <td class="text-right">{{ number_format($grandTotal, 2, ',', ' ') }} €</td>
            </tr>
        </tfoot>
    </table>

    @if($supplierOrder->notes)
    <div class="section-title">Notes</div>
    <p style="color:#374151;">{{ $supplierOrder->notes }}</p>
    @endif
</div>

<div class="footer">
    Document généré le {{ now()->format('d/m/Y à H:i') }} — {{ $shopName }} — {{ $shopEmail }}
</div>

</body>
</html>
