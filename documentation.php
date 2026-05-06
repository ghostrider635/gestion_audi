<?php
require_once __DIR__ . "/includes/fonctions_auth.php";
require_once __DIR__ . "/includes/fonctions_ui.php";
exiger_connexion();
demarrer_session();
$utilisateur = $_SESSION["utilisateur"] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation SGA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="print-header">
        SGA - Faculte des Sciences Informatiques - Documentation academique
    </div>
    <div class="print-footer">
        Annee 2025-2026 - Universite Protestante au Congo
    </div>
    <div class="page">
        <section class="doc-title-page">
            <p class="badge">TRAVAUX DIRIGES - PROGRAMMATION WEB PHP</p>
            <h1>Systeme de Gestion des Auditoires et des Horaires</h1>
            <p class="subtitle">Faculte des Sciences Informatiques - Universite Protestante au Congo</p>
            <p class="subtitle">Promotion L2 - Rapport technique et fonctionnel</p>
            <div class="doc-tags">
                <span>Version du document: 1.0</span>
                <span>Date: Avril 2026</span>
                <span>Auteur: Equipe projet SGA</span>
            </div>
        </section>

        <header class="hero doc-hero">
            <div>
                <p class="badge">DOCUMENTATION PROJET</p>
                <h1>Systeme de Gestion des Auditoires (SGA)</h1>
                <p class="subtitle">Version premium imprimable pour le rendu academique</p>
                <p class="subtitle">Faculte des Sciences Informatiques - UPC - Annee 2025-2026</p>
            </div>
            <div class="action-form">
                <a class="btn btn-ghost" href="index.php">Retour application</a>
                <button type="button" class="btn btn-primary" onclick="window.print()">Exporter en PDF</button>
            </div>
        </header>

        <section class="panel" id="sommaire">
            <h2>Sommaire</h2>
            <ol class="doc-toc">
                <li><a href="#identification">Identification du projet</a></li>
                <li><a href="#fonctionnalites">Fonctionnalites implementees</a></li>
                <li><a href="#architecture">Architecture et roles</a></li>
                <li><a href="#roadmap">Fonctionnalites a finaliser</a></li>
                <li><a href="#comptes">Comptes de connexion</a></li>
                <li><a href="#galerie">Galerie visuelle</a></li>
                <li><a href="#session">Utilisateur connecte</a></li>
            </ol>
        </section>

        <section class="doc-cover">
            <div class="doc-cover-text">
                <h2>Presentation du projet</h2>
                <p>
                    Le SGA automatise la planification des cours selon les capacites des salles, les promotions,
                    les options et les contraintes de disponibilite.
                </p>
                <div class="doc-tags">
                    <span>PHP Procedural</span>
                    <span>JSON</span>
                    <span>Planning Hebdomadaire</span>
                    <span>Gestion des Roles</span>
                </div>
            </div>
            <div class="doc-cover-media">
                <img src="assets/images/doc-cover.jpg" alt="Campus universitaire moderne">
            </div>
        </section>

        <section class="panel" id="identification">
            <h2>Identification du projet</h2>
            <p>
                Projet realise en PHP procedural avec persistance fichiers JSON, pour automatiser la repartition
                des cours dans les auditoires selon les capacites et les contraintes de planning.
            </p>
        </section>

        <section class="panel" id="fonctionnalites">
            <h2>Fonctionnalites implementees</h2>
            <ul>
                <li>Chargement des donnees depuis fichiers JSON (salles, promotions, cours, options).</li>
                <li>Validation des contraintes metier (capacite, disponibilite salle, disponibilite groupe).</li>
                <li>Generation automatique du planning hebdomadaire (lundi a vendredi).</li>
                <li>Sauvegarde du planning en JSON et TXT.</li>
                <li>Affichage HTML moderne du planning avec theme bleu fonce.</li>
                <li>Detection de conflits salle/groupe.</li>
                <li>Rapport d'occupation des salles dans un fichier texte.</li>
                <li>Authentification via page de login + deconnexion securisee.</li>
            </ul>
        </section>

        <section class="doc-grid-2 print-page-break" id="architecture">
            <article class="panel doc-card">
                <h2>Architecture retenue</h2>
                <ul>
                    <li><strong>assets/</strong> : CSS, JS, images</li>
                    <li><strong>includes/</strong> : fonctions procedurales</li>
                    <li><strong>pages/</strong> : dashboards par role</li>
                    <li><strong>donnees/</strong> : persistance JSON</li>
                </ul>
            </article>
            <article class="panel doc-card">
                <h2>Roles utilisateurs</h2>
                <ul>
                    <li><strong>Admin</strong> : gestion complete et generation.</li>
                    <li><strong>Apparitorat</strong> : gestion du planning.</li>
                    <li><strong>Professeur</strong> : consultation du planning.</li>
                    <li><strong>Visiteur</strong> : lecture uniquement.</li>
                </ul>
            </article>
        </section>

        <section class="panel" id="roadmap">
            <h2>Fonctionnalites a finaliser (roadmap)</h2>
            <ul>
                <li>Edition manuelle d'une affectation avec verification immediate.</li>
                <li>Gestion des permissions par role (admin, apparitorat, enseignant, visiteur).</li>
                <li>Formulaires complets de saisie/modification des donnees depuis l'interface.</li>
                <li>Journal des operations (historique des generations et modifications).</li>
            </ul>
        </section>

        <section class="panel" id="comptes">
            <h2>Comptes de connexion de demonstration</h2>
            <table>
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Login</th>
                        <th>Mot de passe initial</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Administrateur</td><td>admin</td><td>Admin@2026</td></tr>
                    <tr><td>Apparitorat</td><td>apparitorat</td><td>Appari@2026</td></tr>
                    <tr><td>Enseignant</td><td>enseignant</td><td>Teacher@2026</td></tr>
                    <tr><td>Visiteur</td><td>visiteur</td><td>Viewer@2026</td></tr>
                </tbody>
            </table>
            <p class="subtitle">Les mots de passe sont stockes sous forme hachee dans users.json.</p>
        </section>

        <section class="panel" id="galerie">
            <h2>Galerie visuelle du contexte projet</h2>
            <div class="doc-gallery">
                <figure>
                    <img src="assets/images/doc-gallery-1.jpg" alt="Etudiants en salle de classe">
                    <figcaption>Salles de cours et organisation des promotions.</figcaption>
                </figure>
                <figure>
                    <img src="assets/images/doc-gallery-2.jpg" alt="Ordinateurs dans un laboratoire">
                    <figcaption>Laboratoire informatique pour cours pratiques.</figcaption>
                </figure>
                <figure>
                    <img src="assets/images/doc-gallery-3.jpg" alt="Tableau de planning academique">
                    <figcaption>Planification hebdomadaire et gestion intelligente.</figcaption>
                </figure>
            </div>
        </section>

        <section class="panel" id="session">
            <h2>Utilisateur connecte</h2>
            <p>
                Session active : <strong><?= htmlspecialchars($utilisateur["nom"] ?? "-", ENT_QUOTES, "UTF-8"); ?></strong>
                (<?= htmlspecialchars($utilisateur["role"] ?? "-", ENT_QUOTES, "UTF-8"); ?>)
            </p>
        </section>
    </div>
</body>
</html>

