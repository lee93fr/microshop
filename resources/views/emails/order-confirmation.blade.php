<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 20px; color: #1e293b; }
.container { max-width: 580px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.header { background: #4f46e5; color: white; padding: 24px 32px; }
.header h1 { margin: 0; font-size: 18px; }
.body { padding: 32px; }
.badge { display: inline-block; padding: 6px 18px; border-radius: 20px; background: #e0e7ff; color: #3730a3; font-weight: 700; font-size: 14px; }
.box { background: #f8fafc; border-radius: 8px; padding: 16px; margin: 20px 0; font-size: 14px; }
.box table { width: 100%; border-collapse: collapse; }
.box td { padding: 5px 0; border-bottom: 1px solid #e2e8f0; }
.box tr:last-child td { border-bottom: none; }
.box td:first-child { color: #64748b; width: 45%; }
.box td:last-child { font-weight: 600; text-align: right; }
.items table { width: 100%; border-collapse: collapse; font-size: 13px; }
.items th { text-align: left; padding: 8px; background: #f1f5f9; color: #64748b; font-weight: 600; }
.items td { padding: 8px; border-bottom: 1px solid #f1f5f9; }
.items td:last-child { text-align: right; font-weight: 600; }
.total-row td { font-weight: 700; font-size: 15px; border-top: 2px solid #e2e8f0; padding-top: 12px; }
.btn { display: inline-block; padding: 12px 28px; background: #4f46e5; color: white !important; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 14px; }
.footer { padding: 16px 32px; background: #f8fafc; font-size: 11px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🍾 La Tournée! — Confirmation de commande</h1>
    </div>
    <div class="body">
        <p>Bonjour <strong>{{ $order->user->name }}</strong>,</p>
        <p>Votre commande a bien été enregistrée. Merci pour votre confiance !</p>

        <div class="box">
            <table>
                <tr><td>Référence</td><td>{{ $order->reference }}</td></tr>
                <tr><td>Date</td><td>{{ $order->created_at->format('d/m/Y à H:i') }}</td></tr>
                <tr><td>Mode de livraison</td><td>{{ $order->delivery_mode === 'pickup' ? 'Retrait sur place' : 'Livraison à domicile' }}</td></tr>
                <tr><td>Mode de paiement</td><td>{{ $order->payment_method_label ?? $order->payment_method }}</td></tr>
            </table>
        </div>

        <div class="items">
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th style="text-align:center">Qté</th>
                        <th style="text-align:right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? 'Produit' }}</td>
                        <td style="text-align:center">{{ $item->quantity }}</td>
                        <td style="text-align:right">{{ number_format($item->line_total, 2, ',', ' ') }} €</td>
                    </tr>
                    @endforeach
                    @if($order->delivery_fee > 0)
                    <tr>
                        <td colspan="2" style="color:#64748b">Frais de livraison</td>
                        <td style="text-align:right">{{ number_format($order->delivery_fee, 2, ',', ' ') }} €</td>
                    </tr>
                    @endif
                    @if($order->credit_used > 0)
                    <tr>
                        <td colspan="2" style="color:#6366f1">Avoir déduit</td>
                        <td style="text-align:right; color:#6366f1">−{{ number_format($order->credit_used, 2, ',', ' ') }} €</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="2">Total</td>
                        <td>{{ number_format($order->total, 2, ',', ' ') }} €</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($order->payment_link && $order->payment_status !== 'paid')
        <div style="margin: 24px 0; padding: 16px; background: #eff6ff; border-radius: 8px; border: 1px solid #bfdbfe;">
            <p style="margin:0 0 12px; font-size:14px; color:#1e40af;">Vous avez choisi le paiement en ligne. Finalisez votre paiement en cliquant ci-dessous :</p>
            <a href="{{ $order->payment_link }}" class="btn" style="background:#1d4ed8;">Payer maintenant →</a>
        </div>
        @endif

        <div style="text-align:center; margin-top: 28px;">
            <a href="{{ route('client.orders.show', $order) }}" class="btn">Suivre ma commande</a>
        </div>

        <p style="margin-top:24px; font-size:13px; color:#64748b;">
            Vous avez une question ? Contactez-nous par email ou consultez votre espace client.
        </p>
    </div>
    <div class="footer">
        © {{ date('Y') }} La Tournée! — Vous recevez cet email car vous avez passé une commande.
    </div>
</div>
</body>
</html>
