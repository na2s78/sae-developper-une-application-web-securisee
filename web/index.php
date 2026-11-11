<?php
declare(strict_types=1);

require_once __DIR__ . '/src/autoloader.php';

use touiteur\Connexion;
use touiteur\Dispatcher;
use touiteur\CompteConnex;
use touiteur\actions\DetailAction;
use touiteur\actions\FollowAction;
use touiteur\actions\FormulaireAction;
use touiteur\actions\HomeAction;
use touiteur\actions\TagAction;
use touiteur\actions\UserAction;

session_start();

try {
    Connexion::setConfig(__DIR__ . '/conf/db.ini');
    $pdo = Connexion::makeConnection();
} catch (\Exception $e) {
    // Affiche l'erreur PDO si la connexion avec la BDD ne marche pas mais normalement plus d'erreur
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

$d = new Dispatcher($pdo);
$d->addRoute('default', HomeAction::class)
  ->addRoute('home', HomeAction::class)
  ->addRoute('mur', HomeAction::class)
  ->addRoute('detail', DetailAction::class)
  ->addRoute('user', UserAction::class)
  ->addRoute('profil', UserAction::class)
  ->addRoute('tag', TagAction::class)
  ->addRoute('formulaire', FormulaireAction::class)
  ->addRoute('follow', FollowAction::class)
  ->addRoute('connexion', CompteConnex::class)
  ->addRoute('deconnexion', CompteConnex::class);

$d->run();
