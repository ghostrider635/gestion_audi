<?php
require_once __DIR__ . "/includes/fonctions_auth.php";

deconnecter_utilisateur();
header("Location: login.php");
exit;

