<?php

function indexer_par_id($elements)
{
    $index = [];
    foreach ($elements as $element) {
        if (isset($element["id"])) {
            $index[$element["id"]] = $element;
        }
    }
    return $index;
}

function creer_creneaux_disponibles()
{
    $jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"];
    $blocs = [["08:00", "12:00"], ["13:00", "17:00"]];
    $creneaux = [];
    foreach ($jours as $jour) {
        foreach ($blocs as $bloc) {
            $creneaux[] = [
                "id" => $jour . "_" . $bloc[0],
                "jour" => $jour,
                "debut" => $bloc[0],
                "fin" => $bloc[1]
            ];
        }
    }
    return $creneaux;
}

function salle_disponible($planning, $id_salle, $creneau_id)
{
    foreach ($planning as $item) {
        if ($item["salle_id"] === $id_salle && $item["creneau_id"] === $creneau_id) {
            return false;
        }
    }
    return true;
}

function capacite_suffisante($salles, $id_salle, $effectif)
{
    foreach ($salles as $salle) {
        if ($salle["id"] === $id_salle) {
            return (int) $effectif <= (int) $salle["capacite"];
        }
    }
    return false;
}

function creneau_libre_groupe($planning, $id_groupe, $creneau_id)
{
    foreach ($planning as $item) {
        if ($item["groupe_id"] === $id_groupe && $item["creneau_id"] === $creneau_id) {
            return false;
        }
    }
    return true;
}

function generer_planning($salles, $promotions, $cours, $options, $creneaux_disponibles)
{
    usort($salles, function ($a, $b) {
        return (int) $a["capacite"] <=> (int) $b["capacite"];
    });
    $promotions_par_id = indexer_par_id($promotions);
    $options_par_id = indexer_par_id($options);
    $planning = [];
    $non_planifies = [];

    foreach ($cours as $cours_item) {
        $type = $cours_item["type"];
        $promotion_id = $cours_item["promotion"];
        $option_id = $cours_item["option"] ?? null;

        if ($type === "option") {
            if (!isset($options_par_id[$option_id])) {
                $non_planifies[] = $cours_item["id"] . " (option manquante)";
                continue;
            }
            $groupe_id = $option_id;
            $effectif = (int) $options_par_id[$option_id]["effectif"];
        } else {
            if (!isset($promotions_par_id[$promotion_id])) {
                $non_planifies[] = $cours_item["id"] . " (promotion manquante)";
                continue;
            }
            $groupe_id = $promotion_id;
            $effectif = (int) $promotions_par_id[$promotion_id]["effectif"];
        }

        $place = false;
        foreach ($creneaux_disponibles as $creneau) {
            if (!creneau_libre_groupe($planning, $groupe_id, $creneau["id"])) {
                continue;
            }
            foreach ($salles as $salle) {
                if (
                    salle_disponible($planning, $salle["id"], $creneau["id"]) &&
                    capacite_suffisante($salles, $salle["id"], $effectif)
                ) {
                    $planning[] = [
                        "jour" => $creneau["jour"],
                        "debut" => $creneau["debut"],
                        "fin" => $creneau["fin"],
                        "creneau_id" => $creneau["id"],
                        "salle_id" => $salle["id"],
                        "cours_id" => $cours_item["id"],
                        "cours_intitule" => $cours_item["intitule"],
                        "groupe_id" => $groupe_id,
                        "type_cours" => $type,
                        "effectif" => $effectif
                    ];
                    $place = true;
                    break;
                }
            }
            if ($place) {
                break;
            }
        }
        if (!$place) {
            $non_planifies[] = $cours_item["id"] . " (sans creneau)";
        }
    }

    return ["planning" => $planning, "non_planifies" => $non_planifies];
}

function construire_grille_planning($planning)
{
    $grille = [];
    foreach ($planning as $item) {
        $cle = $item["jour"] . "|" . $item["debut"] . "-" . $item["fin"];
        $grille[$cle][] = $item;
    }
    return $grille;
}

function detecter_conflits($planning)
{
    $conflits = [];
    $total = count($planning);
    for ($i = 0; $i < $total; $i++) {
        for ($j = $i + 1; $j < $total; $j++) {
            if ($planning[$i]["creneau_id"] !== $planning[$j]["creneau_id"]) {
                continue;
            }
            if ($planning[$i]["salle_id"] === $planning[$j]["salle_id"]) {
                $conflits[] = "Conflit salle " . $planning[$i]["salle_id"] . " sur " . $planning[$i]["creneau_id"];
            }
            if ($planning[$i]["groupe_id"] === $planning[$j]["groupe_id"]) {
                $conflits[] = "Conflit groupe " . $planning[$i]["groupe_id"] . " sur " . $planning[$i]["creneau_id"];
            }
        }
    }
    return array_values(array_unique($conflits));
}

