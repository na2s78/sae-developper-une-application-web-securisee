<?php

require_once "Connexion.php";
require_once "GestionTouite.php";

$pdo = Connexion::getPDO();

$gestion = new GestionTouite($pdo);

$touites = $gestion->getTousLesTouites();

foreach ($touites as $touite) {
    echo $touite["texteTouite"] . "<br>";
}