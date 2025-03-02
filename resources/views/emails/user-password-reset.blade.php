<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #007bff;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 20px;
            text-align: center;
        }

        .content p {
            font-size: 16px;
            color: #333333;
        }

        .my_button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            color: #ffffff !important;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 4px;
        }

        .footer {
            margin-top: 20px;
            padding: 10px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Réinitialisation de mot de passe</h1>
        </div>
        <div class="content">
            <p>Bonjour {{ $first_name }},</p>
            <p>Vous avez demandé la réinitialisation de votre mot de passe sur notre site 'site-name'. Pour réinitialiser votre mot de passe, veuillez cliquer sur le bouton ci-dessous :</p>
            <a href="http://localhost:5173/auth/reset-password?token={{ $password_reset_token }}" class="my_button">Réinitialiser mon mot de passe</a>
            <p>Si vous n'avez pas fait cette demande, veuillez ignorer cet email.</p>
        </div>
        <div class="footer">
            <p>© 2024 'site-name'. Tous droits réservés.</p>
            <p>Adresse</p>
        </div>
    </div>
</body>

</html>
