<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 20px; color: #1f2937; }
        .container { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; }
        .header { background: #4f46e5; padding: 28px 32px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 20px; }
        .body { padding: 32px; }
        .btn { display: inline-block; background: #4f46e5; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: bold; margin: 20px 0; }
        .note { font-size: 12px; color: #9ca3af; margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>La Tournée!</h1>
        </div>
        <div class="body">
            <p>Bonjour {{ $user->name }},</p>
            <p>Vous avez demandé à changer votre adresse email vers <strong>{{ $newEmail }}</strong>.</p>
            <p>Cliquez sur le bouton ci-dessous pour confirmer cette nouvelle adresse :</p>
            <a href="{{ $verificationUrl }}" class="btn">Confirmer ma nouvelle adresse</a>
            <p>Ce lien est valide pendant <strong>24 heures</strong>.</p>
            <p>Si vous n'avez pas effectué cette demande, ignorez cet email. Votre adresse email actuelle reste inchangée.</p>
            <div class="note">
                Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
                <span style="color:#4f46e5; word-break:break-all;">{{ $verificationUrl }}</span>
            </div>
        </div>
    </div>
</body>
</html>
