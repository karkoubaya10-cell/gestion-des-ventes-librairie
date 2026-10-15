<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Librairie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <main class="login-card">
        <a class="brand login-brand" href="{{ route('login') }}" aria-label="Librairie, gestion des ventes">
            <span class="brand-mark">L</span>
            <span><strong>LIBRAIRIE</strong><small>Gestion des ventes</small></span>
        </a>
        <div class="login-intro">
            <p class="login-kicker">ESPACE PROFESSIONNEL</p>
            <h1>Connexion</h1>
            <p class="heading-description">Connectez-vous pour acc&eacute;der &agrave; votre espace.</p>
        </div>
        @if ($errors->any())
            <div class="alert-error" role="alert">{{ $errors->first() }}</div>
        @endif
        <form action="{{ route('login.store') }}" method="POST" class="login-form">
            @csrf
            <label class="form-field">Adresse e-mail
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="nom@exemple.com" required autofocus>
            </label>
            <label class="form-field">Mot de passe
                <input type="password" name="password" autocomplete="current-password" placeholder="Votre mot de passe" required>
            </label>
            <button class="button button-primary" type="submit">Se connecter</button>
        </form>
        <p class="login-footer">Librairie <span>&middot;</span> Gestion des ventes</p>
    </main>
</body>
</html>
