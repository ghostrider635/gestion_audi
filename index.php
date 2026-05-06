<?php
require_once __DIR__ . "/includes/fonctions_auth.php";
exiger_connexion();
$u = utilisateur_courant();

if (($u["role"] ?? "") === "admin") {
    header("Location: pages/dashboard_admin.php");
    exit;
}
if (($u["role"] ?? "") === "apparitorat") {
    header("Location: pages/dashboard_appariteur.php");
    exit;
}
header("Location: pages/vue_professeur.php");
exit;

