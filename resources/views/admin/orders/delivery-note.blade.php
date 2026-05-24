<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de livraison — {{ $order->reference }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            background: #f5f5f5;
        }

        .page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 20mm 18mm;
            position: relative;
        }

        /* ── En-tête ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 16px;
            border-bottom: 3px solid #1a1a1a;
            margin-bottom: 24px;
        }
        .shop-name {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #1a1a1a;
        }
        .shop-meta {
            font-size: 11px;
            color: #555;
            margin-top: 4px;
            line-height: 1.6;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h1 {
            font-size: 22px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1a1a1a;
        }
        .doc-title .ref {
            font-size: 13px;
            color: #555;
            margin-top: 4px;
            font-family: monospace;
        }
        .doc-title .date {
            font-size: 11px;
            color: #888;
            margin-top: 2px;
        }

        /* ── Bloc adresses ── */
        .addresses {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        .address-box {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 14px 16px;
        }
        .address-box .label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 8px;
        }
        .address-box .name {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .address-box p {
            font-size: 12px;
            color: #444;
            line-height: 1.7;
        }

        /* ── Info commande ── */
        .order-meta {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .meta-chip {
            display: inline-flex;
            flex-direction: column;
            padding: 8px 14px;
            background: #f8f8f8;
            border: 1px solid #e8e8e8;
            border-radius: 6px;
            font-size: 11px;
        }
        .meta-chip .chip-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #888;
            margin-bottom: 2px;
        }
        .meta-chip .chip-value {
            font-weight: 600;
            color: #1a1a1a;
        }

        /* ── Tableau produits ── */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .products-table thead tr {
            background: #1a1a1a;
            color: #fff;
        }
        .products-table th {
            padding: 10px 14px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .products-table th.right { text-align: right; }
        .products-table th.center { text-align: center; }
        .products-table tbody tr {
            border-bottom: 1px solid #eee;
        }
        .products-table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        .products-table td {
            padding: 10px 14px;
            font-size: 12px;
            color: #333;
        }
        .products-table td.center { text-align: center; font-weight: 600; }
        .products-table td.right  { text-align: right; }
        .products-table td.product-name { font-weight: 600; color: #1a1a1a; }
        .products-table td .product-sub { font-size: 10px; color: #888; font-weight: 400; margin-top: 1px; }

        /* ── Récap livraison ── */
        .delivery-recap {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 14px 16px;
            margin-bottom: 24px;
            background: #f9f9f9;
        }
        .delivery-recap .label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 8px;
        }
        .delivery-recap p {
            font-size: 12px;
            color: #333;
            line-height: 1.7;
        }

        /* ── Notes ── */
        .notes-box {
            border: 1px dashed #ccc;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 28px;
        }
        .notes-box .label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 6px;
        }
        .notes-box p { font-size: 12px; color: #444; }

        /* ── Signatures ── */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 32px;
        }
        .sig-block {
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }
        .sig-block .sig-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #666;
            margin-bottom: 40px;
        }
        .sig-block .sig-date {
            font-size: 10px;
            color: #aaa;
        }

        /* ── Pied de page ── */
        .footer {
            position: absolute;
            bottom: 14mm;
            left: 18mm;
            right: 18mm;
            border-top: 1px solid #eee;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #aaa;
        }

        /* ── Bouton impression ── */
        .print-bar {
            background: #1a1a1a;
            color: #fff;
            text-align: center;
            padding: 12px;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            justify-content: center;
            gap: 16px;
            align-items: center;
        }
        .print-bar button {
            background: #fff;
            color: #1a1a1a;
            border: none;
            padding: 8px 24px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
        }
        .print-bar a {
            color: #aaa;
            font-size: 12px;
            text-decoration: none;
        }
        .print-bar a:hover { color: #fff; }

        @media print {
            body { background: #fff; }
            .print-bar { display: none; }
            .page { margin: 0; padding: 14mm 16mm; box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="print-bar">
    <span style="font-size:12px;">Bon de livraison — {{ $order->reference }}</span>
    <button onclick="window.print()">🖨️ Imprimer / PDF</button>
    <a href="{{ route('admin.commandes.show', $order) }}">← Retour à la commande</a>
</div>

<div class="page">

    {{-- En-tête --}}
    <div class="header">
        <div>
            <div class="shop-name">{{ $settings['shop_name'] ?? config('app.name') }}</div>
            <div class="shop-meta">
                @if(!empty($settings['shop_email'])){{ $settings['shop_email'] }}<br>@endif
                @if(!empty($settings['shop_phone'])){{ $settings['shop_phone'] }}<br>@endif
                @if(!empty($settings['shop_address'])){{ $settings['shop_address'] }}@endif
            </div>
        </div>
        <div class="doc-title">
            <h1>Bon de livraison</h1>
            <div class="ref">{{ $order->reference }}</div>
            <div class="date">Émis le {{ $order->created_at->format('d/m/Y') }}</div>
        </div>
    </div>

    {{-- Adresses --}}
    <div class="addresses">
        <div class="address-box">
            <div class="label">Expéditeur</div>
            <div class="name">{{ $settings['shop_name'] ?? config('app.name') }}</div>
            @if(!empty($settings['shop_address']))
                <p>{{ $settings['shop_address'] }}</p>
            @endif
            @if(!empty($settings['shop_email']))
                <p>{{ $settings['shop_email'] }}</p>
            @endif
        </div>
        <div class="address-box">
            <div class="label">Destinataire</div>
            <div class="name">{{ $order->user->name }}</div>
            <p>
                @if($order->delivery_mode === 'home')
                    {{ $order->delivery_address }}<br>
                    {{ $order->delivery_postal_code }} {{ $order->delivery_city }}<br>
                    {{ $order->delivery_country }}
                @else
                    Retrait sur place
                @endif
            </p>
            @if($order->user->email)
                <p>{{ $order->user->email }}</p>
            @endif
            @if($order->user->phone)
                <p>{{ $order->user->phone }}</p>
            @endif
        </div>
    </div>

    {{-- Méta commande --}}
    <div class="order-meta">
        <div class="meta-chip">
            <span class="chip-label">Référence</span>
            <span class="chip-value">{{ $order->reference }}</span>
        </div>
        <div class="meta-chip">
            <span class="chip-label">Date commande</span>
            <span class="chip-value">{{ $order->created_at->format('d/m/Y') }}</span>
        </div>
        <div class="meta-chip">
            <span class="chip-label">Mode de livraison</span>
            <span class="chip-value">{{ $order->delivery_mode === 'pickup' ? 'Retrait sur place' : 'Livraison à domicile' }}</span>
        </div>
        <div class="meta-chip">
            <span class="chip-label">Statut</span>
            <span class="chip-value">{{ strip_tags($order->status_label) }}</span>
        </div>
    </div>

    {{-- Produits --}}
    <table class="products-table">
        <thead>
            <tr>
                <th style="width:50%">Désignation</th>
                <th class="center" style="width:10%">Qté</th>
                <th style="width:15%">Unité</th>
                <th style="width:12%">Volume</th>
                <th class="right" style="width:13%">P.U. TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td class="product-name">
                    {{ $item->product->name }}
                    @if($item->product->description)
                        <div class="product-sub">{{ Str::limit($item->product->description, 80) }}</div>
                    @endif
                </td>
                <td class="center">{{ $item->quantity }}</td>
                <td>{{ ucfirst($item->product->unit ?? 'bouteille') }}</td>
                <td>{{ $item->product->volume_ml ? $item->product->volume_ml . ' ml' : '—' }}</td>
                <td class="right">{{ number_format($item->unit_price, 2, ',', ' ') }} €</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="border-top: 2px solid #1a1a1a; background:#fff;">
                <td colspan="4" style="padding:10px 14px; text-align:right; font-size:11px; color:#555; text-transform:uppercase; letter-spacing:.5px;">
                    Sous-total
                </td>
                <td class="right" style="padding:10px 14px; font-weight:600;">{{ number_format($order->subtotal, 2, ',', ' ') }} €</td>
            </tr>
            @if($order->delivery_fee > 0)
            <tr style="background:#fff;">
                <td colspan="4" style="padding:4px 14px; text-align:right; font-size:11px; color:#555;">Frais de livraison</td>
                <td class="right" style="padding:4px 14px;">{{ number_format($order->delivery_fee, 2, ',', ' ') }} €</td>
            </tr>
            @endif
            @if($order->discount > 0)
            <tr style="background:#fff;">
                <td colspan="4" style="padding:4px 14px; text-align:right; font-size:11px; color:#c00;">Remise</td>
                <td class="right" style="padding:4px 14px; color:#c00;">−{{ number_format($order->discount, 2, ',', ' ') }} €</td>
            </tr>
            @endif
            <tr style="background:#1a1a1a; color:#fff;">
                <td colspan="4" style="padding:12px 14px; text-align:right; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.5px;">
                    Total TTC
                </td>
                <td style="padding:12px 14px; text-align:right; font-size:15px; font-weight:800;">
                    {{ number_format($order->total, 2, ',', ' ') }} €
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Mode de livraison --}}
    @if($order->delivery_mode === 'home')
    <div class="delivery-recap">
        <div class="label">Adresse de livraison</div>
        <p>
            <strong>{{ $order->user->name }}</strong><br>
            {{ $order->delivery_address }}<br>
            {{ $order->delivery_postal_code }} {{ $order->delivery_city }}<br>
            {{ $order->delivery_country }}
        </p>
    </div>
    @endif

    {{-- Notes --}}
    @if($order->notes)
    <div class="notes-box">
        <div class="label">Notes client</div>
        <p>{{ $order->notes }}</p>
    </div>
    @endif

    {{-- Signatures --}}
    <div class="signatures">
        <div class="sig-block">
            <div class="sig-label">Signature du livreur</div>
            <div class="sig-date">Date : _____ / _____ / _________</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Signature du destinataire<br><span style="font-weight:400;font-size:9px;">Bon pour réception, lu et approuvé</span></div>
            <div class="sig-date">Date : _____ / _____ / _________</div>
        </div>
    </div>

    {{-- Pied de page --}}
    <div class="footer">
        <span>{{ $settings['shop_name'] ?? config('app.name') }}</span>
        <span>{{ $order->reference }} — émis le {{ now()->format('d/m/Y') }}</span>
        <span>1 / 1</span>
    </div>

</div>
</body>
</html>
