<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>Code de vérification ShiftUp</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light dark;
            supported-color-schemes: light dark;
        }
        body {
            font-family: 'Jost', 'Helvetica Neue', Helvetica, Arial, sans-serif !important;
            background-color: #070707;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 10px;
        }
        .logo {
            height: 40px;
            width: auto;
        }
        .card {
            background-color: #ffffff !important;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
            padding-bottom: 30px;
        }
        .card-content {
            padding: 25px 40px;
        }
        .greeting {
            font-size: 32px;
            font-weight: 600;
            color: #070707;
            letter-spacing: -0.5px;
            margin-bottom: 15px;
            text-align: center;
        }
        .instruction {
            font-size: 18px;
            line-height: 1.4;
            color: #444;
            text-align: center;
            margin-bottom: 30px;
        }
        .otp-box {
            background: #f8f9fa;
            border: 2px dashed #8A38F5;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin: 20px auto;
            max-width: 250px;
        }
        .otp-code {
            font-size: 42px;
            font-weight: 800;
            color: #8A38F5;
            letter-spacing: 10px;
            margin: 0;
        }
        .footer-text {
            font-size: 14px;
            color: #888;
            text-align: center;
            margin-top: 30px;
            padding: 0 40px;
        }
        .brand-gradient {
            height: 4px;
            background: linear-gradient(90deg, #0E7EC3, #8A38F5);
        }
    </style>
</head>
<body>
    <div class="container">            
        <div class="card">
            <div class="brand-gradient"></div>
            <div style="padding: 40px 40px 0 40px; text-align: center;">
                <img src="https://res.cloudinary.com/dzgdjei0h/image/upload/v1770630247/logo-site-blanc_ee9hbg.png" alt="ShiftUp" class="logo" style="filter: brightness(0);">
            </div>

            <div class="card-content">
                <div class="greeting">{{ $title ?? 'Vérification de votre compte' }}</div>
                <div class="instruction">
                    {{ $messageText ?? 'Merci de vous être inscrit sur ShiftUp. Pour finaliser la création de votre compte, veuillez saisir le code de sécurité suivant :' }}
                </div>
                
                <div class="otp-box">
                    <h1 class="otp-code">{{ $code }}</h1>
                </div>

                <div class="instruction" style="font-size: 14px; margin-top: 20px;">
                    Ce code est valable pendant 10 minutes. Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail.
                </div>
            </div>

            <div class="footer-text">
                &copy; {{ date('Y') }} ShiftUp. Tous droits réservés.
            </div>
        </div>
    </div>
</body>
</html>
