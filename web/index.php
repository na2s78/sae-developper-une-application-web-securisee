<?php
declare(strict_types=1);

require_once __DIR__ . '/src/autoloader.php';

use touiteur\Connexion;
use touiteur\Dispatcher;
use touiteur\actions\HomeAction;

session_start();

try {
    Connexion::setConfig(__DIR__ . '/conf/db.ini');
    $pdo = Connexion::makeConnection();
} catch (\Exception $e) {
    // Affiche l'erreur PDO exacte s'il y a un souci de base de données
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

$d = new Dispatcher($pdo);
$d->addRoute('default', HomeAction::class)
  ->addRoute('home', HomeAction::class);

$d->run();