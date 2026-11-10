<?php
declare(strict_types=1);

require_once __DIR__ . '/src/autoloader.php';

use touiteur\Connexion;
use touiteur\Dispatcher;
use touiteur\actions\HomeAction;
use touiteur\actions\LikeAction;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    Connexion::setConfig(__DIR__ . '/conf/db.ini');
    $pdo = Connexion::makeConnection();
} catch (Exception $e) {
    die('Erreur de connexion à la base de données.');
}

$d = new Dispatcher($pdo);
$d->addRoute('default', HomeAction::class)
  ->addRoute('home', HomeAction::class);
  ->addRoute('like', LikeAction::class);

$d->run();