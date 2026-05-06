<?php
require_once __DIR__ . "/../includes/fonctions_auth.php";
require_once __DIR__ . "/../includes/fonctions_fichiers.php";
require_once __DIR__ . "/../includes/fonctions_planning.php";
require_once __DIR__ . "/../includes/fonctions_ui.php";
exiger_role("admin");

$messages = [];
$errors = [];
$non_planifies = [];
$utilisateurs = [];
$u = utilisateur_courant();

try {
    $salles = charger_salles(chemin_donnee("salles.json"));
    $promotions = charger_promotions(chemin_donnee("promotions.json"));
    $cours = charger_cours(chemin_donnee("cours.json"));
    $options = charger_options(chemin_donnee("options.json"));
    $planning = charger_planning(chemin_donnee("planning.json"));
    $utilisateurs = charger_utilisateurs();
} catch (Exception $e) {
    $errors[] = $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($errors)) {
    $action = $_POST["action"] ?? "";
    try {
        if ($action === "generate") {
            $resultat = generer_planning($salles, $promotions, $cours, $options, creer_creneaux_disponibles());
            $planning = $resultat["planning"];
            $non_planifies = $resultat["non_planifies"];
            sauvegarder_planning($planning, chemin_donnee("planning.json"));
            $messages[] = "Planning genere et sauvegarde.";
        } elseif ($action === "add_user") {
            $nouveau = [
                "id" => "U" . str_pad((string) random_int(100, 999), 3, "0", STR_PAD_LEFT),
                "nom" => trim($_POST["nom"] ?? ""),
                "role" => trim($_POST["role"] ?? "visiteur"),
                "login" => trim($_POST["login"] ?? ""),
                "password_hash" => password_hash(trim($_POST["password"] ?? ""), PASSWORD_DEFAULT)
            ];
            if ($nouveau["nom"] === "" || $nouveau["login"] === "" || trim($_POST["password"] ?? "") === "") {
                throw new Exception("Tous les champs utilisateur sont obligatoires.");
            }
            ajouter_entree_json(chemin_donnee("users.json"), $nouveau);
            $messages[] = "Utilisateur ajoute avec succes.";
        } elseif ($action === "update_user") {
            $utilisateur_id = trim($_POST["utilisateur_id"] ?? "");
            $existant = null;
            foreach ($utilisateurs as $u_item) {
                if (($u_item["id"] ?? "") === $utilisateur_id) {
                    $existant = $u_item;
                    break;
                }
            }
            if (!$existant) {
                throw new Exception("Utilisateur introuvable.");
            }
            $mot_de_passe = trim($_POST["password"] ?? "");
            $maj = [
                "id" => $existant["id"],
                "nom" => trim($_POST["nom"] ?? ""),
                "role" => trim($_POST["role"] ?? "visiteur"),
                "login" => trim($_POST["login"] ?? ""),
                "password_hash" => $mot_de_passe !== "" ? password_hash($mot_de_passe, PASSWORD_DEFAULT) : ($existant["password_hash"] ?? "")
            ];
            if ($maj["nom"] === "" || $maj["login"] === "") {
                throw new Exception("Nom et login obligatoires pour la mise a jour.");
            }
            mettre_a_jour_entree_json(chemin_donnee("users.json"), $utilisateur_id, $maj);
            $messages[] = "Utilisateur mis a jour.";
        } elseif ($action === "delete_user") {
            $utilisateur_id = trim($_POST["utilisateur_id"] ?? "");
            if ($utilisateur_id === ($u["id"] ?? "")) {
                throw new Exception("Vous ne pouvez pas supprimer votre propre compte.");
            }
            supprimer_entree_json(chemin_donnee("users.json"), $utilisateur_id);
            $messages[] = "Utilisateur supprime.";
        } elseif ($action === "add_course") {
            $nouveau_cours = [
                "id" => trim($_POST["cours_id"] ?? ""),
                "intitule" => trim($_POST["intitule"] ?? ""),
                "volume_horaire" => (int) ($_POST["volume_horaire"] ?? 4),
                "type" => trim($_POST["type"] ?? "tronc_commun"),
                "promotion" => trim($_POST["promotion"] ?? ""),
                "option" => ($_POST["option"] ?? "") !== "" ? trim($_POST["option"]) : null
            ];
            if ($nouveau_cours["id"] === "" || $nouveau_cours["intitule"] === "" || $nouveau_cours["promotion"] === "") {
                throw new Exception("Champs cours incomplets.");
            }
            ajouter_entree_json(chemin_donnee("cours.json"), $nouveau_cours);
            $messages[] = "Cours ajoute avec succes.";
        } elseif ($action === "update_course") {
            $cours_id = trim($_POST["cours_id"] ?? "");
            $maj_cours = [
                "id" => $cours_id,
                "intitule" => trim($_POST["intitule"] ?? ""),
                "volume_horaire" => (int) ($_POST["volume_horaire"] ?? 4),
                "type" => trim($_POST["type"] ?? "tronc_commun"),
                "promotion" => trim($_POST["promotion"] ?? ""),
                "option" => ($_POST["option"] ?? "") !== "" ? trim($_POST["option"]) : null
            ];
            if ($cours_id === "" || $maj_cours["intitule"] === "" || $maj_cours["promotion"] === "") {
                throw new Exception("Champs cours incomplets pour la mise a jour.");
            }
            mettre_a_jour_entree_json(chemin_donnee("cours.json"), $cours_id, $maj_cours);
            $messages[] = "Cours mis a jour.";
        } elseif ($action === "delete_course") {
            $cours_id = trim($_POST["cours_id"] ?? "");
            supprimer_entree_json(chemin_donnee("cours.json"), $cours_id);
            $messages[] = "Cours supprime.";
        } elseif ($action === "add_room") {
            $nouvelle_salle = [
                "id" => trim($_POST["salle_id"] ?? ""),
                "designation" => trim($_POST["designation"] ?? ""),
                "capacite" => (int) ($_POST["capacite"] ?? 0)
            ];
            if ($nouvelle_salle["id"] === "" || $nouvelle_salle["designation"] === "" || $nouvelle_salle["capacite"] <= 0) {
                throw new Exception("Champs salle incomplets.");
            }
            ajouter_entree_json(chemin_donnee("salles.json"), $nouvelle_salle);
            $messages[] = "Salle ajoutee.";
        } elseif ($action === "update_room") {
            $salle_id = trim($_POST["salle_id"] ?? "");
            $maj_salle = [
                "id" => $salle_id,
                "designation" => trim($_POST["designation"] ?? ""),
                "capacite" => (int) ($_POST["capacite"] ?? 0)
            ];
            if ($salle_id === "" || $maj_salle["designation"] === "" || $maj_salle["capacite"] <= 0) {
                throw new Exception("Champs salle invalides pour la mise a jour.");
            }
            mettre_a_jour_entree_json(chemin_donnee("salles.json"), $salle_id, $maj_salle);
            $messages[] = "Salle mise a jour.";
        } elseif ($action === "delete_room") {
            $salle_id = trim($_POST["salle_id"] ?? "");
            supprimer_entree_json(chemin_donnee("salles.json"), $salle_id);
            $messages[] = "Salle supprimee.";
        } elseif ($action === "add_promotion") {
            $nouvelle_promotion = [
                "id" => trim($_POST["promotion_id"] ?? ""),
                "libelle" => trim($_POST["libelle"] ?? ""),
                "effectif" => (int) ($_POST["effectif"] ?? 0)
            ];
            if ($nouvelle_promotion["id"] === "" || $nouvelle_promotion["libelle"] === "" || $nouvelle_promotion["effectif"] <= 0) {
                throw new Exception("Champs promotion incomplets.");
            }
            ajouter_entree_json(chemin_donnee("promotions.json"), $nouvelle_promotion);
            $messages[] = "Promotion ajoutee.";
        } elseif ($action === "update_promotion") {
            $promotion_id = trim($_POST["promotion_id"] ?? "");
            $maj_promotion = [
                "id" => $promotion_id,
                "libelle" => trim($_POST["libelle"] ?? ""),
                "effectif" => (int) ($_POST["effectif"] ?? 0)
            ];
            if ($promotion_id === "" || $maj_promotion["libelle"] === "" || $maj_promotion["effectif"] <= 0) {
                throw new Exception("Champs promotion invalides pour la mise a jour.");
            }
            mettre_a_jour_entree_json(chemin_donnee("promotions.json"), $promotion_id, $maj_promotion);
            $messages[] = "Promotion mise a jour.";
        } elseif ($action === "delete_promotion") {
            $promotion_id = trim($_POST["promotion_id"] ?? "");
            foreach ($cours as $cours_item) {
                if (($cours_item["promotion"] ?? "") === $promotion_id) {
                    throw new Exception("Suppression impossible: promotion utilisee par des cours.");
                }
            }
            foreach ($options as $option_item) {
                if (($option_item["promotion_parent"] ?? "") === $promotion_id) {
                    throw new Exception("Suppression impossible: promotion liee a des options.");
                }
            }
            supprimer_entree_json(chemin_donnee("promotions.json"), $promotion_id);
            $messages[] = "Promotion supprimee.";
        } elseif ($action === "add_option") {
            $nouvelle_option = [
                "id" => trim($_POST["option_id"] ?? ""),
                "libelle" => trim($_POST["libelle"] ?? ""),
                "promotion_parent" => trim($_POST["promotion_parent"] ?? ""),
                "effectif" => (int) ($_POST["effectif"] ?? 0)
            ];
            if ($nouvelle_option["id"] === "" || $nouvelle_option["libelle"] === "" || $nouvelle_option["promotion_parent"] === "" || $nouvelle_option["effectif"] <= 0) {
                throw new Exception("Champs option incomplets.");
            }
            ajouter_entree_json(chemin_donnee("options.json"), $nouvelle_option);
            $messages[] = "Option ajoutee.";
        } elseif ($action === "update_option") {
            $option_id = trim($_POST["option_id"] ?? "");
            $maj_option = [
                "id" => $option_id,
                "libelle" => trim($_POST["libelle"] ?? ""),
                "promotion_parent" => trim($_POST["promotion_parent"] ?? ""),
                "effectif" => (int) ($_POST["effectif"] ?? 0)
            ];
            if ($option_id === "" || $maj_option["libelle"] === "" || $maj_option["promotion_parent"] === "" || $maj_option["effectif"] <= 0) {
                throw new Exception("Champs option invalides pour la mise a jour.");
            }
            mettre_a_jour_entree_json(chemin_donnee("options.json"), $option_id, $maj_option);
            $messages[] = "Option mise a jour.";
        } elseif ($action === "delete_option") {
            $option_id = trim($_POST["option_id"] ?? "");
            foreach ($cours as $cours_item) {
                if (($cours_item["option"] ?? "") === $option_id) {
                    throw new Exception("Suppression impossible: option utilisee par des cours.");
                }
            }
            supprimer_entree_json(chemin_donnee("options.json"), $option_id);
            $messages[] = "Option supprimee.";
        }

        $salles = charger_salles(chemin_donnee("salles.json"));
        $promotions = charger_promotions(chemin_donnee("promotions.json"));
        $cours = charger_cours(chemin_donnee("cours.json"));
        $options = charger_options(chemin_donnee("options.json"));
        $planning = charger_planning(chemin_donnee("planning.json"));
        $utilisateurs = charger_utilisateurs();
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

$grille = construire_grille_planning($planning ?? []);
$conflits = detecter_conflits($planning ?? []);
$jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"];
$blocs = ["08:00-12:00", "13:00-17:00"];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SGA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script defer src="../assets/js/animations.js"></script>
</head>
<body>
<div class="page">
    <header class="hero page-hero admin-hero">
        <div>
            <p class="badge">Dashboard Admin</p>
            <h1>Bienvenue <?= h($u["nom"]); ?></h1>
            <p class="subtitle">Gestion complete: planning, cours, utilisateurs.</p>
        </div>
        <div class="action-form">
            <form method="post"><button class="btn btn-primary" name="action" value="generate">Generer planning</button></form>
            <a class="btn btn-ghost" href="../documentation.php">Doc PDF</a>
            <a class="btn btn-danger" href="../logout.php">Deconnexion</a>
        </div>
    </header>

    <section class="hero-gallery">
        <article class="hero-card-media">
            <img src="../assets/images/admin-hero-1.jpg" alt="Administration universitaire">
            <div class="hero-card-caption">Pilotage administratif des ressources pedagogiques</div>
        </article>
        <article class="hero-card-media">
            <img src="../assets/images/admin-hero-2.jpg" alt="Tableau de bord numerique">
            <div class="hero-card-caption">Tableau de bord moderne pour la decision rapide</div>
        </article>
    </section>

    <?php foreach ($messages as $m): ?><div class="alert alert-success toastify"><?= h($m); ?></div><?php endforeach; ?>
    <?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e); ?></div><?php endforeach; ?>

    <section class="panel">
        <h2>Ajout rapide d'un utilisateur</h2>
        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="add_user">
            <input name="nom" placeholder="Nom complet">
            <input name="login" placeholder="Login">
            <input name="password" placeholder="Mot de passe initial">
            <select name="role"><option value="admin">Admin</option><option value="apparitorat">Apparitorat</option><option value="professeur">Professeur</option><option value="visiteur">Visiteur</option></select>
            <button class="btn btn-primary" type="submit">Ajouter utilisateur</button>
        </form>
    </section>

    <section class="panel">
        <h2>Ajout rapide d'un cours</h2>
        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="add_course">
            <input name="cours_id" placeholder="Code cours (ex: WEB-L3)">
            <input name="intitule" placeholder="Intitule du cours">
            <input name="volume_horaire" type="number" min="4" value="4">
            <select name="type"><option value="tronc_commun">Tronc commun</option><option value="option">Option</option></select>
            <select name="promotion"><option value="L1">L1</option><option value="L2">L2</option><option value="L3">L3</option><option value="L4">L4</option></select>
            <input name="option" placeholder="Option (OPT-SEC...)">
            <button class="btn btn-primary" type="submit">Ajouter cours</button>
        </form>
    </section>

    <section class="panel">
        <h2>CRUD Utilisateurs (Admin)</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>ID</th><th>Nom</th><th>Role</th><th>Login</th><th>Mot de passe</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($utilisateurs as $utilisateur_item): ?>
                    <tr>
                        <form method="post">
                            <td>
                                <?= h($utilisateur_item["id"]); ?>
                                <input type="hidden" name="utilisateur_id" value="<?= h($utilisateur_item["id"]); ?>">
                            </td>
                            <td><input name="nom" value="<?= h($utilisateur_item["nom"]); ?>"></td>
                            <td>
                                <select name="role">
                                    <option value="admin" <?= ($utilisateur_item["role"] === "admin") ? "selected" : ""; ?>>Admin</option>
                                    <option value="apparitorat" <?= ($utilisateur_item["role"] === "apparitorat") ? "selected" : ""; ?>>Apparitorat</option>
                                    <option value="enseignant" <?= ($utilisateur_item["role"] === "enseignant") ? "selected" : ""; ?>>Professeur</option>
                                    <option value="visiteur" <?= ($utilisateur_item["role"] === "visiteur") ? "selected" : ""; ?>>Visiteur</option>
                                </select>
                            </td>
                            <td><input name="login" value="<?= h($utilisateur_item["login"]); ?>"></td>
                            <td><input name="password" placeholder="Laisser vide pour conserver"></td>
                            <td class="crud-actions">
                                <button class="btn btn-primary" type="submit" name="action" value="update_user">Mettre a jour</button>
                                <button class="btn btn-danger" type="submit" name="action" value="delete_user">Supprimer</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <h2>CRUD Cours (Admin)</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>ID</th><th>Intitule</th><th>Volume</th><th>Type</th><th>Promotion</th><th>Option</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($cours as $cours_item): ?>
                    <tr>
                        <form method="post">
                            <td><input name="cours_id" value="<?= h($cours_item["id"]); ?>" readonly></td>
                            <td><input name="intitule" value="<?= h($cours_item["intitule"]); ?>"></td>
                            <td><input name="volume_horaire" type="number" min="1" value="<?= h($cours_item["volume_horaire"]); ?>"></td>
                            <td>
                                <select name="type">
                                    <option value="tronc_commun" <?= ($cours_item["type"] === "tronc_commun") ? "selected" : ""; ?>>Tronc commun</option>
                                    <option value="option" <?= ($cours_item["type"] === "option") ? "selected" : ""; ?>>Option</option>
                                </select>
                            </td>
                            <td><input name="promotion" value="<?= h($cours_item["promotion"]); ?>"></td>
                            <td><input name="option" value="<?= h($cours_item["option"] ?? ""); ?>"></td>
                            <td class="crud-actions">
                                <button class="btn btn-primary" type="submit" name="action" value="update_course">Mettre a jour</button>
                                <button class="btn btn-danger" type="submit" name="action" value="delete_course">Supprimer</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <h2>CRUD Salles (Admin)</h2>
        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="add_room">
            <input name="salle_id" placeholder="ID salle (ex: AUD-L5)">
            <input name="designation" placeholder="Designation de la salle">
            <input name="capacite" type="number" min="1" placeholder="Capacite">
            <button class="btn btn-primary" type="submit">Ajouter salle</button>
        </form>
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>ID</th><th>Designation</th><th>Capacite</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($salles as $salle_item): ?>
                    <tr>
                        <form method="post">
                            <td><input name="salle_id" value="<?= h($salle_item["id"]); ?>" readonly></td>
                            <td><input name="designation" value="<?= h($salle_item["designation"]); ?>"></td>
                            <td><input name="capacite" type="number" min="1" value="<?= h($salle_item["capacite"]); ?>"></td>
                            <td class="crud-actions">
                                <button class="btn btn-primary" type="submit" name="action" value="update_room">Mettre a jour</button>
                                <button class="btn btn-danger" type="submit" name="action" value="delete_room">Supprimer</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <h2>CRUD Promotions (Admin)</h2>
        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="add_promotion">
            <input name="promotion_id" placeholder="ID promotion (ex: L5)">
            <input name="libelle" placeholder="Libelle promotion">
            <input name="effectif" type="number" min="1" placeholder="Effectif">
            <button class="btn btn-primary" type="submit">Ajouter promotion</button>
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr><th>ID</th><th>Libelle</th><th>Effectif</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($promotions as $promotion_item): ?>
                    <tr>
                        <form method="post">
                            <td><input name="promotion_id" value="<?= h($promotion_item["id"]); ?>" readonly></td>
                            <td><input name="libelle" value="<?= h($promotion_item["libelle"]); ?>"></td>
                            <td><input name="effectif" type="number" min="1" value="<?= h($promotion_item["effectif"]); ?>"></td>
                            <td class="crud-actions">
                                <button class="btn btn-primary" type="submit" name="action" value="update_promotion">Mettre a jour</button>
                                <button class="btn btn-danger" type="submit" name="action" value="delete_promotion">Supprimer</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <h2>CRUD Options (Admin)</h2>
        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="add_option">
            <input name="option_id" placeholder="ID option (ex: OPT-NEW)">
            <input name="libelle" placeholder="Libelle option">
            <select name="promotion_parent">
                <?php foreach ($promotions as $promotion_item): ?>
                    <option value="<?= h($promotion_item["id"]); ?>"><?= h($promotion_item["id"]); ?></option>
                <?php endforeach; ?>
            </select>
            <input name="effectif" type="number" min="1" placeholder="Effectif">
            <button class="btn btn-primary" type="submit">Ajouter option</button>
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr><th>ID</th><th>Libelle</th><th>Promotion parent</th><th>Effectif</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($options as $option_item): ?>
                    <tr>
                        <form method="post">
                            <td><input name="option_id" value="<?= h($option_item["id"]); ?>" readonly></td>
                            <td><input name="libelle" value="<?= h($option_item["libelle"]); ?>"></td>
                            <td>
                                <select name="promotion_parent">
                                    <?php foreach ($promotions as $promotion_item): ?>
                                        <option value="<?= h($promotion_item["id"]); ?>" <?= (($option_item["promotion_parent"] ?? "") === ($promotion_item["id"] ?? "")) ? "selected" : ""; ?>>
                                            <?= h($promotion_item["id"]); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input name="effectif" type="number" min="1" value="<?= h($option_item["effectif"]); ?>"></td>
                            <td class="crud-actions">
                                <button class="btn btn-primary" type="submit" name="action" value="update_option">Mettre a jour</button>
                                <button class="btn btn-danger" type="submit" name="action" value="delete_option">Supprimer</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php if (!empty($conflits)): ?><section class="panel"><h2>Conflits</h2><ul><?php foreach ($conflits as $c): ?><li><?= h($c); ?></li><?php endforeach; ?></ul></section><?php endif; ?>
    <?php if (!empty($non_planifies)): ?><section class="panel"><h2>Non planifies</h2><ul><?php foreach ($non_planifies as $n): ?><li><?= h($n); ?></li><?php endforeach; ?></ul></section><?php endif; ?>

    <section class="panel">
        <h2>Planning</h2>
        <div class="table-wrap"><table><thead><tr><th>Creneau</th><?php foreach ($jours as $j): ?><th><?= h($j); ?></th><?php endforeach; ?></tr></thead>
            <tbody><?php foreach ($blocs as $b): ?><tr><th><?= h($b); ?></th><?php foreach ($jours as $j): $cle = $j . "|" . $b; ?><td><?php if (!empty($grille[$cle])): foreach ($grille[$cle] as $entry): ?><div class="slot-card"><div class="slot-title"><?= h($entry["cours_id"]); ?> - <?= h($entry["cours_intitule"]); ?></div><div class="slot-badge <?= classes_badge_type($entry["type_cours"]); ?>"><?= h($entry["type_cours"]); ?></div><div class="slot-meta">Salle: <?= h($entry["salle_id"]); ?></div><div class="slot-meta">Groupe: <?= h($entry["groupe_id"]); ?></div></div><?php endforeach; else: ?><span class="empty">Libre</span><?php endif; ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody>
        </table></div>
    </section>

    <section class="panel">
        <h2>Galerie du dashboard admin</h2>
        <div class="dashboard-gallery">
            <figure>
                <img src="../assets/images/admin-gallery-1.jpg" alt="Reunion de coordination">
                <figcaption>Coordination academique entre les equipes</figcaption>
            </figure>
            <figure>
                <img src="../assets/images/admin-gallery-2.jpg" alt="Analyse des donnees de gestion">
                <figcaption>Analyse et suivi des donnees du planning</figcaption>
            </figure>
            <figure>
                <img src="../assets/images/admin-gallery-3.jpg" alt="Tableau de gestion numerique">
                <figcaption>Pilotage numerique des decisions administratives</figcaption>
            </figure>
        </div>
    </section>
</div>
</body>
</html>

