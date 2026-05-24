<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 20px; color: #1e293b; }
.container { max-width: 580px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.header { background: #1e293b; color: white; padding: 24px 32px; }
.header h1 { margin: 0; font-size: 18px; }
.body { padding: 32px; }
.box { background: #f8fafc; border-radius: 8px; padding: 16px; margin: 20px 0; font-size: 14px; }
.box table { width: 100%; border-collapse: collapse; }
.box td { padding: 5px 0; border-bottom: 1px solid #e2e8f0; }
.box tr:last-child td { border-bottom: none; }
.box td:first-child { color: #64748b; width: 45%; }
.box td:last-child { font-weight: 600; text-align: right; }
.btn { display: inline-block; padding: 12px 28px; background: #1e293b; color: white !important; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 14px; }
.footer { padding: 16px 32px; background: #f8fafc; font-size: 11px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🛒 Nouvelle commande reçue — La Tournée!</h1>
    </div>
    <div class="body">
        <p>Une nouvelle commande vient d'être passée sur la boutique.</p>

        <div class="box">
            <table>
                <tr><td>Référence</td><td>{{ $order->reference }}</td></tr>
                <tr><td>Client</td><td>{{ $order->user->name }}</td></tr>
                <tr><td>Email client</td><td>{{ $order->user->email }}</td></tr>
                <tr><td>Date</td><td>{{ $order->created_at->format('d/m/Y à H:i') }}</td></tr>
                <tr><td>Livraison</td><td>{{ $order->delivery_mode === 'pickup' ? 'Retrait sur place' : 'Livraison à domicile' }}</td></tr>
                <tr><td>Paiement</td><td>{{ $order->payment_method }}</td></tr>
                <tr><td>Total</td><td>{{ number_format($order->total, 2, ',', ' ') }} €</td></tr>
                <tr><td>Articles</td><td>{{ $order->items->count() }} article(s)</td></tr>
            </table>
        </div>

        @if($order->notes)
        <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:12px 16px; font-size:13px; color:#92400e; margin-bottom:16px;">
            <strong>Note client :</strong> {{ $order->notes }}
        </div>
        @endif

        <div style="text-align:center; margin-top: 24px;">
            <a href="{{ url('/admin/commandes/' . $order->id) }}" class="btn">Voir la commande →</a>
        </div>
    </div>
    <div class="footer">
        © {{ date('Y') }} La Tournée! — Notification interne.
    </div>
</div>
</body>
</html>
