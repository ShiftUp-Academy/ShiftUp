<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message Contact</title>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Jost', sans-serif; background-color: #070707; margin: 0; padding: 20px; color: #333; }
        .card { background: #ffffff; border-radius: 12px; max-width: 600px; margin: 0 auto; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
        .header { padding: 30px; background: #000; text-align: center; }
        .logo { height: 40px; }
        .content { padding: 40px; }
        .info-row { margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .label { font-weight: 700; color: #8A38F5; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
        .value { font-size: 16px; color: #111; margin-top: 5px; }
        .message-box { background: #f9f9f9; padding: 20px; border-radius: 8px; border-left: 4px solid #8A38F5; margin-top: 20px; font-style: italic; white-space: pre-line; }
        .footer { text-align: center; padding: 20px; color: #888; font-size: 12px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <img src="https://res.cloudinary.com/dzgdjei0h/image/upload/v1770630247/logo-site-blanc_ee9hbg.png" alt="ShiftUp" class="logo">
        </div>
        <div class="content">
            <h2 style="margin-top: 0;">Nouveau message de contact</h2>
            <div class="info-row">
                <div class="label">Expéditeur</div>
                <div class="value">{{ $prenom }} {{ $nom }}</div>
            </div>
            <div class="info-row">
                <div class="label">Email</div>
                <div class="value">{{ $email }}</div>
            </div>
            <div class="info-row">
                <div class="label">Sujet</div>
                <div class="value">{{ $sujet }}</div>
            </div>
            <div class="label">Message</div>
            <div class="message-box">
                {{ $messageBody }}
            </div>
        </div>
    </div>
    <div class="footer">
        Envoyé depuis le formulaire de contact de ShiftUp Academy
    </div>
</body>
</html>
