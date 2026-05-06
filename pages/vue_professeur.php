<?php
require_once __DIR__ . "/../includes/fonctions_auth.php";
require_once __DIR__ . "/../includes/fonctions_fichiers.php";
require_once __DIR__ . "/../includes/fonctions_planning.php";
require_once __DIR__ . "/../includes/fonctions_ui.php";
exiger_role(["admin", "enseignant", "professeur", "visiteur"]);

$planning = charger_planning(chemin_donnee("planning.json"));
$grille = construire_grille_planning($planning);
$u = utilisateur_courant();
$jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"];
$blocs = ["08:00-12:00", "13:00-17:00"];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vue Professeur - SGA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script defer src="../assets/js/animations.js"></script>
</head>
<body>
<div class="page">
    <header class="hero page-hero professeur-hero">
        <div><p class="badge">Vue Professeur</p><h1><?= h($u["nom"]); ?></h1><p class="subtitle">Consultation en temps reel du planning.</p></div>
        <div class="action-form"><a class="btn btn-ghost" href="../documentation.php">Doc PDF</a><a class="btn btn-danger" href="../logout.php">Deconnexion</a></div>
    </header>
    <section class="hero-gallery">
        <article class="hero-card-media">
            <img src="../assets/images/prof-hero-1.jpg" alt="Professeur avec etudiants">
            <div class="hero-card-caption">Vue simplifiee pour consulter rapidement les cours</div>
        </article>
        <article class="hero-card-media">
            <img src="../assets/images/prof-hero-2.jpg" alt="Planning de cours">
            <div class="hero-card-caption">Acces direct au planning hebdomadaire en salle</div>
        </article>
    </section>
    <section class="panel">
        <h2>Responsabilites du role professeur</h2>
        <p class="subtitle">
            Ce role est en consultation uniquement: lecture du planning, sans creation, modification ni suppression.
        </p>
    </section>
    <section class="panel">
        <h2>Planning hebdomadaire</h2>
        <div class="table-wrap"><table><thead><tr><th>Creneau</th><?php foreach ($jours as $j): ?><th><?= h($j); ?></th><?php endforeach; ?></tr></thead>
            <tbody><?php foreach ($blocs as $b): ?><tr><th><?= h($b); ?></th><?php foreach ($jours as $j): $cle = $j . "|" . $b; ?><td><?php if (!empty($grille[$cle])): foreach ($grille[$cle] as $entry): ?><div class="slot-card"><div class="slot-title"><?= h($entry["cours_id"]); ?> - <?= h($entry["cours_intitule"]); ?></div><div class="slot-meta">Salle: <?= h($entry["salle_id"]); ?></div></div><?php endforeach; else: ?><span class="empty">Libre</span><?php endif; ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody>
        </table></div>
    </section>
    <section class="panel">
        <h2>Galerie pedagogique</h2>
        <div class="dashboard-gallery">
            <figure>
                <img src="../assets/images/prof-gallery-1.jpg" alt="Classe active avec enseignants">
                <figcaption>Interaction en classe et suivi des enseignements</figcaption>
            </figure>
            <figure>
                <img src="../assets/images/prof-gallery-2.jpg" alt="Etudiants en cours magistral">
                <figcaption>Visibilite claire des creneaux de cours</figcaption>
            </figure>
            <figure>
                <img src="../assets/images/prof-gallery-3.jpg" alt="Salle de cours universitaire">
                <figcaption>Preparation des seances selon les salles disponibles</figcaption>
            </figure>
        </div>
    </section>
</div>
</body>
</html>

