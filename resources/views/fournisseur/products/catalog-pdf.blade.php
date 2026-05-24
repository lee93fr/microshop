<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; background: #fff; }

        .header { background: #4F46E5; color: white; padding: 20px 24px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; font-weight: bold; letter-spacing: 0.5px; }
        .header .subtitle { font-size: 11px; opacity: 0.85; margin-top: 4px; }
        .header .meta { font-size: 9px; opacity: 0.7; margin-top: 8px; }

        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #f3f4f6;
            padding: 7px 8px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
        }
        thead th.right { text-align: right; }
        tbody tr { border-bottom: 1px solid #f3f4f6; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody td { padding: 6px 8px; vertical-align: middle; }
        tbody td.right { text-align: right; font-weight: bold; }
        tbody td.center { text-align: center; }

        .badge-new {
            display: inline-block;
            background: #f59e0b;
            color: white;
            font-size: 7px;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 8px;
        }
        .badge-stock-out {
            display: inline-block;
            background: #fee2e2;
            color: #dc2626;
            font-size: 7px;
            padding: 1px 4px;
            border-radius: 4px;
        }
        .category-badge {
            display: inline-block;
            background: #e0e7ff;
            color: #4338ca;
            font-size: 8px;
            padding: 1px 5px;
            border-radius: 4px;
        }
        .category-section { margin-bottom: 24px; }
        .category-header {
            background: #4F46E5;
            color: white;
            padding: 7px 10px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 0;
            page-break-after: avoid;
        }
        .category-count { font-weight: normal; opacity: 0.65; font-size: 9px; }
        .footer { margin-top: 16px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 8px; color: #9ca3af; text-align: center; }
        .price { color: #111827; }
    </style>
</head>
<body>
    <div class="header">
        <h1>La Tournée! — Catalogue fournisseur</h1>
        <div class="subtitle">Prix d'achat fournisseur — Document confidentiel</div>
        <div class="meta">Généré le {{ $generatedAt }} · {{ $products->count() }} produit(s)</div>
    </div>

    @php $byCategory = $products->groupBy(fn($p) => $p->category->name ?? 'Sans catégorie'); @endphp

    @foreach($byCategory as $categoryName => $items)
    <div class="category-section">
        <div class="category-header">{{ $categoryName }} <span class="category-count">({{ $items->count() }} produit{{ $items->count() > 1 ? 's' : '' }})</span></div>
        <table>
            <thead>
                <tr>
                    <th style="width:32%">Produit</th>
                    <th>SKU</th>
                    <th>Volume</th>
                    <th>Degré</th>
                    <th class="right">Prix achat</th>
                    <th class="center">Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $product)
                <tr>
                    <td>
                        {{ $product->name }}
                        @if($product->is_new) <span class="badge-new">NEW</span> @endif
                    </td>
                    <td style="font-family: monospace; color: #9ca3af; font-size: 8px;">{{ $product->sku ?: '—' }}</td>
                    <td>{{ $product->volume_ml ? $product->volume_ml . ' ml' : '—' }}</td>
                    <td>{{ $product->alcohol_degree ? $product->alcohol_degree . '°' : '—' }}</td>
                    <td class="right price">{{ number_format($product->purchase_price, 2, ',', ' ') }} €</td>
                    <td class="center">
                        @if(!$product->in_stock)
                            <span class="badge-stock-out">Rupture</span>
                        @else
                            <span style="color:#10b981;">✓</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    <div class="footer">
        La Tournée! — Document confidentiel réservé au fournisseur — Prix d'achat HT
    </div>
</body>
</html>
