<?php

function h($valeur)
{
    return htmlspecialchars((string) $valeur, ENT_QUOTES, "UTF-8");
}

function classes_badge_type($type)
{
    return $type === "option" ? "badge-option" : "badge-core";
}

