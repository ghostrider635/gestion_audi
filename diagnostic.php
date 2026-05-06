<?php
echo "<h2>Diagnostic de gestion_audi</h2>";

echo "<h3>✓ PHP Fonctionnel</h3>";
echo "Version PHP: " . phpversion() . "<br>";

echo "<h3>✓ Sessions</h3>";
echo "Session status: " . (session_status() === PHP_SESSION_ACTIVE ? "ACTIVE" : "INACTIVE") . "<br>";

echo "<h3>✓ Fichiers de configuration</h3>";

$fichiers = [
    "donnees/users.json",
    "donnees/cours.json",
    "donnees/salles.json",
    "donnees/promotions.json",
    "donnees/planning.json",
    "donnees/options.json",
    "includes/fonctions_auth.php",
    "includes/fonctions_fichiers.php",
    "includes/fonctions_ui.php",
    "includes/fonctions_planning.php",
    "includes/functions.php"
];

foreach ($fichiers as $f) {
    $chemin = __DIR__ . "/" . $f;
    $existe = file_exists($chemin) ? "✓ OUI" : "✗ MANQUANT";
    echo $f . ": " . $existe . "<br>";
}

echo "<h3>✓ Test des includes</h3>";
try {
    require_once __DIR__ . "/includes/fonctions_auth.php";
    echo "✓ fonctions_auth.php chargé<br>";
} catch (Exception $e) {
    echo "✗ Erreur fonctions_auth.php: " . $e->getMessage() . "<br>";
}

echo "<h3>✓ Test de connexion</h3>";
try {
    $users = charger_utilisateurs();
    echo "✓ " . count($users) . " utilisateurs chargés<br>";
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "<br>";
}
?>
