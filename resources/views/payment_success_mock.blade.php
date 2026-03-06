<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Réussi (Simulation)</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #050505;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .container {
            background: #111;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            max-width: 500px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        h1 { color: #4CAF50; font-size: 2em; margin-bottom: 10px; }
        p { color: #ccc; line-height: 1.6; }
        .provider { font-weight: bold; color: #8A38F5; }
        .btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 25px;
            background: linear-gradient(130deg, #0d7bc0, #8A38F5);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: bold;
            transition: opacity 0.3s;
        }
        .btn:hover { opacity: 0.8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ Paiement Simulé Réussi !</h1>
        <p>Ceci est une page de test pour l'intégration de <span class="provider">{{ $provider }}</span>.</p>
        <p>Commande N°: {{ $order_id }}</p>
        <p>Ici, un webhook ou une redirection aurait validé le paiement et ajouté l'offre/programme à votre compte.</p>
        <a href="/panier" class="btn">Retour au Panier</a>
    </div>
</body>
</html>
