{{-- resources/views/mails/order-status-updated.blade.php --}}
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
.status { display: inline-block; padding: 6px 18px; border-radius: 20px; background: #e0e7ff; color: #3730a3; font-weight: 700; font-size: 15px; margin: 16px 0; }
.box { background: #f8fafc; border-radius: 8px; padding: 16px; margin: 20px 0; font-size: 14px; }
.box table { width: 100%; border-collapse: collapse; }
.box td { padding: 4px 0; }
.box td:first-child { color: #64748b; width: 40%; }
.btn { display: inline-block; padding: 12px 28px; background: #1e293b; color: white; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 14px; }
.footer { padding: 16px 32px; background: #f8fafc; font-size: 11px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🍾 La Tournée! — Mise à jour de votre commande</h1>
    </div>
    <div class="body">
        <p>Bonjour <strong>{{ $order->user->name }}</strong>,</p>
        <p>Le statut de votre commande <strong>{{ $order->reference }}</strong> a été mis à jour :</p>

        <div style="text-align:center;">
            <span class="status">{{ $order->status_label }}</span>
        </div>

        <div class="box">
            <table>
                <tr><td>Référence</td><td><strong>{{ $order->reference }}</strong></td></tr>
                <tr><td>Total</td><td><strong>{{ number_format($order->total, 2, ',', ' ') }} €</strong></td></tr>
                <tr><td>Paiement</td><td>{{ $order->payment_status_label }}</td></tr>
                @if($order->payment_link && $order->payment_status !== 'paid')
                <tr><td>Lien paiement</td><td><a href="{{ $order->payment_link }}" style="color:#6366f1;">Payer maintenant →</a></td></tr>
                @endif
            </table>
        </div>

        <div style="text-align:center; margin-top: 24px;">
            <a href="{{ route('client.orders.show', $order) }}" class="btn">Voir ma commande</a>
        </div>
    </div>
    <div class="footer">
        Vous recevez cet email car vous avez une commande chez La Tournée!.<br>
        © {{ date('Y') }} La Tournée!
    </div>
</div>
</body>
</html>

