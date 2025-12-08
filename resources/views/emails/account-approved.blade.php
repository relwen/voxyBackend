<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Approuvé - VoXY Box</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, rgb(78, 13, 4), rgb(179, 5, 5), rgb(158, 2, 80));
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .button {
            display: inline-block;
            background: rgb(158, 2, 80);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Félicitations !</h1>
        <h2>Votre compte a été approuvé</h2>
    </div>
    
    <div class="content">
        <p>Bonjour <strong>{{ $user->name }}</strong>,</p>
        
        <p>Nous sommes ravis de vous informer que votre compte VoXY Box a été approuvé avec succès !</p>
        
        <p>Vous pouvez maintenant :</p>
        <ul>
            <li>Vous connecter à l'application</li>
            <li>Accéder à toutes les fonctionnalités</li>
            <li>Profiter de votre expérience musicale</li>
        </ul>
        
        <p>Merci de votre confiance et bonne utilisation de VoXY Box !</p>
        
        <p>Cordialement,<br>
        <strong>L'équipe VoXY Box</strong></p>
    </div>
    
    <div class="footer">
        <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        <p>&copy; {{ date('Y') }} VoXY Box. Tous droits réservés.</p>
    </div>
</body>
</html>

