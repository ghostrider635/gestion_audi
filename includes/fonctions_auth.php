<?php
require_once __DIR__ . "/fonctions_fichiers.php";

function demarrer_session()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function utilisateurs_fichier()
{
    return chemin_donnee("users.json");
}

function charger_utilisateurs()
{
    return lire_json(utilisateurs_fichier());
}

function trouver_utilisateur_par_login($login)
{
    foreach (charger_utilisateurs() as $u) {
        if (($u["login"] ?? "") === $login) {
            return $u;
        }
    }
    return null;
}

function authentifier_utilisateur($login, $mot_de_passe)
{
    $u = trouver_utilisateur_par_login($login);
    if (!$u || !password_verify($mot_de_passe, $u["password_hash"] ?? "")) {
        return [false, null];
    }
    return [true, $u];
}

function utilisateur_connecte()
{
    demarrer_session();
    return !empty($_SESSION["utilisateur"]);
}

function utilisateur_courant()
{
    demarrer_session();
    return $_SESSION["utilisateur"] ?? null;
}

function url_login()
{
    $racine = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"]));
    // Remonter jusqu'à la racine du projet (gestion_audi)
    $script = str_replace("\\", "/", $_SERVER["SCRIPT_FILENAME"]);
    $base = str_replace("\\", "/", realpath(__DIR__ . "/.."));
    $doc_root = str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"]));
    $base_url = substr($base, strlen($doc_root));
    return $base_url . "/login.php";
}

function exiger_connexion()
{
    if (!utilisateur_connecte()) {
        header("Location: " . url_login());
        exit;
    }
}

function exiger_role($roles)
{
    exiger_connexion();
    $roles_autorises = is_array($roles) ? $roles : [$roles];
    $u = utilisateur_courant();
    if (!$u || !in_array($u["role"], $roles_autorises, true)) {
        header("Location: " . url_login() . "?error=acces_refuse");
        exit;
    }
}

function connecter_utilisateur($u)
{
    demarrer_session();
    $_SESSION["utilisateur"] = [
        "id"    => $u["id"],
        "nom"   => $u["nom"],
        "role"  => $u["role"],
        "login" => $u["login"]
    ];
}

function deconnecter_utilisateur()
{
    demarrer_session();
    $_SESSION = [];
    session_destroy();
}
