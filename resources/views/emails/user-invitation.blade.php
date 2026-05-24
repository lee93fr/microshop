<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invitation La Tournée!</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background:#f9fafb; margin:0; padding:40px 20px; color:#374151; }
  .container { max-width:520px; margin:0 auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.1); }
  .header { background:#4f46e5; padding:32px 40px; text-align:center; }
  .header h1 { color:#fff; margin:0; font-size:22px; font-weight:700; letter-spacing:-0.3px; }
  .body { padding:40px; }
  .body p { margin:0 0 16px; line-height:1.6; font-size:15px; }
  .btn { display:inline-block; margin:8px 0 24px; padding:14px 32px; background:#4f46e5; color:#fff !important; text-decoration:none; border-radius:8px; font-weight:600; font-size:15px; }
  .url { word-break:break-all; font-size:12px; color:#9ca3af; margin-top:8px; }
  .footer { padding:24px 40px; border-top:1px solid #f3f4f6; font-size:12px; color:#9ca3af; text-align:center; }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1>La Tournée!</h1>
  </div>
  <div class="body">
    <p>Bonjour {{ $user->name }},</p>
    <p>Vous avez été invité(e) à rejoindre <strong>La Tournée!</strong>. Cliquez sur le bouton ci-dessous pour créer votre mot de passe et accéder à votre compte.</p>
    <p style="text-align:center">
      <a href="{{ $invitationUrl }}" class="btn">Créer mon compte</a>
    </p>
    <p class="url">Ou copiez ce lien dans votre navigateur :<br>{{ $invitationUrl }}</p>
    <p style="color:#9ca3af;font-size:13px">Ce lien est valable 7 jours. Si vous n'attendiez pas cette invitation, ignorez simplement cet email.</p>
  </div>
  <div class="footer">La Tournée! · Gestion de cave</div>
</div>
</body>
</html>

