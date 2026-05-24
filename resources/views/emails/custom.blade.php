<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body { font-family: Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 20px; color: #1e293b; }
.container { max-width: 580px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.header { background: {{ $headerColor ?? '#4f46e5' }}; color: white; padding: 24px 32px; }
.header h1 { margin: 0; font-size: 18px; font-weight: 700; }
.body { padding: 32px; line-height: 1.6; }
.body p { margin: 0 0 16px; }
.body a { color: #4f46e5; }
.footer { padding: 16px 32px; background: #f8fafc; font-size: 11px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $shopName ?? 'La Tournée!' }}</h1>
    </div>
    <div class="body">
        {!! $body !!}
    </div>
    <div class="footer">
        © {{ date('Y') }} {{ $shopName ?? 'La Tournée!' }} — Cet email a été envoyé automatiquement, merci de ne pas y répondre.
    </div>
</div>
</body>
</html>
