<?php
require_once __DIR__ . "/../includes/fonctions_auth.php";
require_once __DIR__ . "/../includes/fonctions_fichiers.php";
require_once __DIR__ . "/../includes/fonctions_planning.php";
require_once __DIR__ . "/../includes/fonctions_ui.php";
exiger_role(["admin", "apparitorat"]);

$messages = [];
$errors = [];

try {
    $salles = charger_salles(chemin_donnee("salles.json"));
    $promotions = charger_promotions(chemin_donnee("promotions.json"));
    $cours = charger_cours(chemin_donnee("cours.json"));
    $options = charger_options(chemin_donnee("options.json"));
    $planning = charger_planning(chemin_donnee("planning.json"));
} catch (Exception $e) {
    $errors[] = $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "generate" && empty($errors)) {
    $resultat = generer_planning($salles, $promotions, $cours, $options, creer_creneaux_disponibles());
    $planning = $resultat["planning"];
    sauvegarder_planning($planning, chemin_donnee("planning.json"));
    $messages[] = "Planning regenere.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($errors) && ($_POST["action"] ?? "") !== "generate") {
    try {
        $action = $_POST["action"] ?? "";
        if ($action === "add_slot") {
            $jour = trim($_POST["jour"] ?? "");
            $debut = trim($_POST["debut"] ?? "");
            $fin = trim($_POST["fin"] ?? "");
            $salle_id = trim($_POST["salle_id"] ?? "");
            $cours_id = trim($_POST["cours_id"] ?? "");
            $groupe_id = trim($_POST["groupe_id"] ?? "");
            $creneau_id = $jour . "_" . $debut;
            if ($jour === "" || $debut === "" || $fin === "" || $salle_id === "" || $cours_id === "" || $groupe_id === "") {
                throw new Exception("Tous les champs sont requis pour ajouter un creneau.");
            }

            $cours_selectionne = null;
            foreach ($cours as $cours_item) {
                if (($cours_item["id"] ?? "") === $cours_id) {
                    $cours_selectionne = $cours_item;
                    break;
                }
            }
            if (!$cours_selectionne) {
                throw new Exception("Cours introuvable.");
            }

            $effectif = 0;
            foreach ($promotions as $promotion) {
                if (($promotion["id"] ?? "") === $groupe_id) {
                    $effectif = (int) ($promotion["effectif"] ?? 0);
                }
            }
            foreach ($options as $option) {
                if (($option["id"] ?? "") === $groupe_id) {
                    $effectif = (int) ($option["effectif"] ?? 0);
                }
            }
            if ($effectif <= 0) {
                throw new Exception("Groupe invalide: effectif introuvable.");
            }
            if (!salle_disponible($planning, $salle_id, $creneau_id)) {
                throw new Exception("Salle deja occupee sur ce creneau.");
            }
            if (!creneau_libre_groupe($planning, $groupe_id, $creneau_id)) {
                throw new Exception("Groupe deja occupe sur ce creneau.");
            }
            if (!capacite_suffisante($salles, $salle_id, $effectif)) {
                throw new Exception("Capacite insuffisante pour ce groupe.");
            }

            $planning[] = [
                "jour" => $jour,
                "debut" => $debut,
                "fin" => $fin,
                "creneau_id" => $creneau_id,
                "salle_id" => $salle_id,
                "cours_id" => $cours_selectionne["id"],
                "cours_intitule" => $cours_selectionne["intitule"],
                "groupe_id" => $groupe_id,
                "type_cours" => $cours_selectionne["type"],
                "effectif" => $effectif
            ];
            sauvegarder_planning($planning, chemin_donnee("planning.json"));
            $messages[] = "Creneau ajoute au planning.";
        } elseif ($action === "delete_slot") {
            $slot_index = (int) ($_POST["slot_index"] ?? -1);
            if (!isset($planning[$slot_index])) {
                throw new Exception("Creneau introuvable pour suppression.");
            }
            unset($planning[$slot_index]);
            $planning = array_values($planning);
            sauvegarder_planning($planning, chemin_donnee("planning.json"));
            $messages[] = "Creneau supprime du planning.";
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

$grille = construire_grille_planning($planning ?? []);
$u = utilisateur_courant();
$jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"];
$blocs = ["08:00-12:00", "13:00-17:00"];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Appariteur - SGA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script defer src="../assets/js/animations.js"></script>
</head>
<body>
<div class="page">
    <header class="hero page-hero appariteur-hero">
        <div><p class="badge">Dashboard Appariteur</p><h1><?= h($u["nom"]); ?></h1><p class="subtitle">Gestion du planning et des salles.</p></div>
        <div class="action-form">
            <form method="post"><button class="btn btn-primary" name="action" value="generate">Generer planning</button></form>
            <a class="btn btn-ghost" href="../documentation.php">Doc PDF</a>
            <a class="btn btn-danger" href="../logout.php">Deconnexion</a>
        </div>
    </header>
    <section class="hero-gallery">
        <article class="hero-card-media">
            <img src="../assets/images/appariteur-hero-1.jpg" alt="Organisation des salles">
            <div class="hero-card-caption">Planification des auditoires et creneaux en un clic</div>
        </article>
        <article class="hero-card-media">
            <img src="../assets/images/appariteur-hero-2.jpg" alt="Coordination equipe">
            <div class="hero-card-caption">Coordination logistique efficace et sans conflit</div>
        </article>
    </section>
    <?php foreach ($messages as $m): ?><div class="alert alert-success"><?= h($m); ?></div><?php endforeach; ?>
    <?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e); ?></div><?php endforeach; ?>
    <section class="panel">
        <h2>CRUD planning (Apparitorat)</h2>
        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="add_slot">
            <select name="jour"><option>Lundi</option><option>Mardi</option><option>Mercredi</option><option>Jeudi</option><option>Vendredi</option></select>
            <select name="debut"><option value="08:00">08:00</option><option value="13:00">13:00</option></select>
            <select name="fin"><option value="12:00">12:00</option><option value="17:00">17:00</option></select>
            <select name="salle_id"><?php foreach ($salles as $s): ?><option value="<?= h($s["id"]); ?>"><?= h($s["id"]); ?></option><?php endforeach; ?></select>
            <select name="cours_id"><?php foreach ($cours as $c): ?><option value="<?= h($c["id"]); ?>"><?= h($c["id"]); ?></option><?php endforeach; ?></select>
            <select name="groupe_id">
                <?php foreach ($promotions as $p): ?><option value="<?= h($p["id"]); ?>"><?= h($p["id"]); ?></option><?php endforeach; ?>
                <?php foreach ($options as $o): ?><option value="<?= h($o["id"]); ?>"><?= h($o["id"]); ?></option><?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit">Ajouter creneau</button>
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr><th>#</th><th>Jour</th><th>Heure</th><th>Salle</th><th>Cours</th><th>Groupe</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($planning as $index => $slot): ?>
                    <tr>
                        <td><?= h($index); ?></td>
                        <td><?= h($slot["jour"]); ?></td>
                        <td><?= h($slot["debut"] . "-" . $slot["fin"]); ?></td>
                        <td><?= h($slot["salle_id"]); ?></td>
                        <td><?= h($slot["cours_id"]); ?></td>
                        <td><?= h($slot["groupe_id"]); ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="action" value="delete_slot">
                                <input type="hidden" name="slot_index" value="<?= h($index); ?>">
                                <button class="btn btn-danger" type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <section class="panel">
        <h2>Planning hebdomadaire</h2>
        <div class="table-wrap"><table><thead><tr><th>Creneau</th><?php foreach ($jours as $j): ?><th><?= h($j); ?></th><?php endforeach; ?></tr></thead>
            <tbody><?php foreach ($blocs as $b): ?><tr><th><?= h($b); ?></th><?php foreach ($jours as $j): $cle = $j . "|" . $b; ?><td><?php if (!empty($grille[$cle])): foreach ($grille[$cle] as $entry): ?><div class="slot-card"><div class="slot-title"><?= h($entry["cours_id"]); ?></div><div class="slot-meta"><?= h($entry["salle_id"]); ?> - <?= h($entry["groupe_id"]); ?></div></div><?php endforeach; else: ?><span class="empty">Libre</span><?php endif; ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody>
        </table></div>
    </section>
    <section class="panel">
        <h2>Galerie de coordination</h2>
        <div class="dashboard-gallery">
            <figure>
                <img src="../assets/images/appariteur-gallery-1.jpg" alt="Equipe de coordination logistique">
                <figcaption>Collaboration entre apparitorat et administration</figcaption>
            </figure>
            <figure>
                <img src="../assets/images/appariteur-gallery-2.jpg" alt="Tableau de suivi des salles">
                <figcaption>Suivi des salles et creneaux en temps reel</figcaption>
            </figure>
            <figure>
                <img src="../assets/images/appariteur-gallery-3.jpg" alt="Organisation d'horaires">
                <figcaption>Organisation efficace des horaires de cours</figcaption>
            </figure>
        </div>
    </section>
</div>
</body>
</html>

