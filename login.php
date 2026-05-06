<?php
require_once __DIR__ . "/includes/fonctions_auth.php";
require_once __DIR__ . "/includes/fonctions_ui.php";

demarrer_session();
$erreur = "";

if (utilisateur_connecte()) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim($_POST["login"] ?? "");
    $mot_de_passe = trim($_POST["password"] ?? "");

    try {
        [$ok, $utilisateur] = authentifier_utilisateur($login, $mot_de_passe);
        if ($ok) {
            connecter_utilisateur($utilisateur);
            header("Location: index.php");
            exit;
        }
        $erreur = "Identifiants invalides. Veuillez reessayer.";
    } catch (Exception $e) {
        $erreur = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SGA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-shell">
            <section class="auth-showcase">
                <p class="badge">UNIVERSITE PROTESTANTE AU CONGO</p>
                <h1>Gestion SGA moderne</h1>
                <p class="subtitle">Organisez les plannings, les salles et les disponibilites dans une interface claire, rapide et securisee.</p>

                <div class="auth-highlights">
                    <span>Planning intelligent</span>
                    <span>Suivi en temps reel</span>
                    <span>Acces selon les roles</span>
                </div>

                <div class="auth-showcase-media">
                    <img src="assets/images/login-hero.jpg" alt="Campus universitaire moderne">
                </div>
                
            </section>

            <section class="auth-card">
                <p class="badge">Connexion securisee</p>
                <h2>Bienvenue</h2>
                <p class="subtitle">Entrez vos identifiants pour acceder au tableau de bord.</p>

                <?php if ($erreur !== ""): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($erreur, ENT_QUOTES, "UTF-8"); ?></div>
                <?php endif; ?>

                <form method="post" class="auth-form">
                    <label for="login">Login</label>
                    <input id="login" name="login" type="text" required autocomplete="username">

                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password">

                    <button type="submit" class="btn btn-primary auth-btn">Se connecter</button>
                </form>

                <div class="small-hint">
                    Comptes de test disponibles dans la documentation du projet.
                </div>
                <div class="small-hint">
                    Interface personnalisee selon votre role.
                </div>
            </section>
        </div>
        <div class="auth-footer-note">
            SGA - Universite Protestante au Congo
        </div>
    </div>
    </body>
    </html>

