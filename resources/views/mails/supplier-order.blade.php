<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 20px; color: #1e293b; }
.container { max-width: 580px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; }
.header { background: #1e293b; color: white; padding: 24px 32px; }
.body { padding: 32px; font-size: 14px; line-height: 1.6; }
.footer { padding: 16px 32px; background: #f8fafc; font-size: 11px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1 style="margin:0; font-size:18px;">🍾 La Tournée! — Bon de commande</h1>
    </div>
    <div class="body">
        <p>Bonjour,</p>
        <p>Veuillez trouver en pièce jointe le bon de commande <strong>{{ $supplierOrder->reference }}</strong>.</p>
        <p>Merci de confirmer sa réception et la disponibilité des produits.</p>
        @if($supplierOrder->notes)
        <p><strong>Notes :</strong> {{ $supplierOrder->notes }}</p>
        @endif
        <p>Cordialement,<br>L'équipe La Tournée!</p>
    </div>
    <div class="footer">© {{ date('Y') }} La Tournée!</div>
</div>
</body>
</html>

