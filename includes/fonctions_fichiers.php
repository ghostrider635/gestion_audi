<?php

function lire_json($chemin_fichier)
{
    if (!file_exists($chemin_fichier)) {
        throw new Exception("Fichier introuvable : {$chemin_fichier}");
    }
    $contenu = file_get_contents($chemin_fichier);
    if ($contenu === false) {
        throw new Exception("Impossible de lire le fichier : {$chemin_fichier}");
    }
    $donnees = json_decode($contenu, true);
    if (!is_array($donnees)) {
        throw new Exception("Format JSON invalide : {$chemin_fichier}");
    }
    return $donnees;
}

function ecrire_json($chemin_fichier, $donnees)
{
    $json = json_encode($donnees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        throw new Exception("Erreur d'encodage JSON : {$chemin_fichier}");
    }
    if (file_put_contents($chemin_fichier, $json) === false) {
        throw new Exception("Impossible d'ecrire : {$chemin_fichier}");
    }
}

function chemin_donnee($nom_fichier)
{
    return __DIR__ . "/../donnees/" . $nom_fichier;
}

function charger_salles($chemin_fichier)
{
    return lire_json($chemin_fichier);
}

function charger_promotions($chemin_fichier)
{
    return lire_json($chemin_fichier);
}

function charger_cours($chemin_fichier)
{
    return lire_json($chemin_fichier);
}

function charger_options($chemin_fichier)
{
    return lire_json($chemin_fichier);
}

function charger_planning($chemin_fichier)
{
    return file_exists($chemin_fichier) ? lire_json($chemin_fichier) : [];
}

function sauvegarder_planning($planning, $chemin_fichier_json)
{
    ecrire_json($chemin_fichier_json, $planning);
}

function ajouter_entree_json($chemin_fichier, $entree)
{
    $liste = file_exists($chemin_fichier) ? lire_json($chemin_fichier) : [];
    $liste[] = $entree;
    ecrire_json($chemin_fichier, $liste);
}

function mettre_a_jour_entree_json($chemin_fichier, $id, $nouvelle_entree)
{
    $liste = file_exists($chemin_fichier) ? lire_json($chemin_fichier) : [];
    $trouve = false;
    foreach ($liste as $index => $entree) {
        if (($entree["id"] ?? "") === $id) {
            $liste[$index] = $nouvelle_entree;
            $trouve = true;
            break;
        }
    }
    if (!$trouve) {
        throw new Exception("Entree introuvable pour mise a jour : " . $id);
    }
    ecrire_json($chemin_fichier, $liste);
}

function supprimer_entree_json($chemin_fichier, $id)
{
    $liste = file_exists($chemin_fichier) ? lire_json($chemin_fichier) : [];
    $taille_initiale = count($liste);
    $liste = array_values(array_filter($liste, function ($entree) use ($id) {
        return ($entree["id"] ?? "") !== $id;
    }));
    if (count($liste) === $taille_initiale) {
        throw new Exception("Entree introuvable pour suppression : " . $id);
    }
    ecrire_json($chemin_fichier, $liste);
}

