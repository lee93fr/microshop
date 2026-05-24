<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 20px; color: #1e293b; }
.container { max-width: 580px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.header { background: #059669; color: white; padding: 24px 32px; }
.header h1 { margin: 0; font-size: 18px; }
.body { padding: 32px; }
.box { background: #f8fafc; border-radius: 8px; padding: 16px; margin: 20px 0; font-size: 14px; }
.box table { width: 100%; border-collapse: collapse; }
.box td { padding: 5px 0; border-bottom: 1px solid #e2e8f0; }
.box tr:last-child td { border-bottom: none; }
.box td:first-child { color: #64748b; width: 45%; }
.box td:last-child { font-weight: 600; text-align: right; }
.btn { display: inline-block; padding: 12px 28px; background: #059669; color: white !important; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 14px; }
.footer { padding: 16px 32px; background: #f8fafc; font-size: 11px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>✅ Paiement confirmé — La Tournée!</h1>
    </div>
    <div class="body">
        <p>Bonjour <strong>{{ $order->user->name }}</strong>,</p>
        <p>Votre paiement a bien été reçu et validé. Votre commande est en cours de traitement.</p>

        <div class="box">
            <table>
                <tr><td>Référence</td><td>{{ $order->reference }}</td></tr>
                <tr><td>Montant payé</td><td>{{ number_format($order->total, 2, ',', ' ') }} €</td></tr>
                <tr><td>Mode de paiement</td><td>{{ $order->payment_method }}</td></tr>
                <tr><td>Statut</td><td>Payé ✓</td></tr>
            </table>
        </div>

        <div style="text-align:center; margin-top: 24px;">
            <a href="{{ route('client.orders.show', $order) }}" class="btn">Voir ma commande</a>
        </div>
    </div>
    <div class="footer">
        © {{ date('Y') }} La Tournée! — Conservez cet email comme justificatif de paiement.
    </div>
</div>
</body>
</html>
