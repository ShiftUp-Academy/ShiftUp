<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>{{ $newsletterSubject ?? 'Newsletter ShiftUp' }}</title>
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
            filter: brightness(0);
        }
        .card {
            font-family: 'Jost', sans-serif !important;
            background-color: #ffffff !important;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
            position: relative;
            z-index: 10;
        }
        
        @media (prefers-color-scheme: dark) {
            body { background-color: #000000 !important; }
        }

        .card-content {
            padding: 25px 40px;
            position: relative;
            z-index: 2;
        }
        .greeting {
            font-size: 32px;
            font-weight: 600;
            color: #070707;
            letter-spacing: -0.5px;
            margin-bottom: 15px;
        }
        .message-body {
            font-size: 18px;
            line-height: 1.4;
            color: #222222;
            text-align: left;
        }
        
        .content-title {
            font-size: 28px;
            line-height: 1.2;
            font-weight: 600;
            color: #070707;
            display: block;
            letter-spacing: -0.5px;
            text-align: left;
            margin-top: 10px;
        }
        
        .footer {
            padding: 20px;
            text-align: center;
            color: #666666;
            font-size: 14px;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(90deg, #0E7EC3, #8A38F5);
            color: #ffffff !important;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 18px;
            text-align: center;
            border: none;
            box-shadow: 0 10px 20px rgba(138, 56, 245, 0.2);
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">            
        <div class="card">
            <div style="padding: 40px 40px 0 40px;">
                <a href="{{ url('/') }}" style="text-decoration:none;">
                    <img src="https://res.cloudinary.com/dzgdjei0h/image/upload/v1770630247/logo-site-blanc_ee9hbg.png" alt="ShiftUp" class="logo">
                </a>
            </div>

            <div class="card-content">
                <div class="greeting">
                    {{ $newsletterSubject }}
                </div>
                
                <div class="message-body">
                    {!! $mailContent !!}
                </div>

                <div style="text-align: center; margin-top: 40px;">
                    <a href="{{ url('/') }}" class="cta-button">
                        Visiter la plateforme
                    </a>
                </div>
            </div>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} ShiftUp Academy. Tous droits réservés.
        </div>
    </div>
</body>
</html>
