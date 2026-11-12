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
use touiteur\actions\LikeAction;
use touiteur\actions\TagAction;
use touiteur\actions\UserAction;

session_start();

try {
    Connexion::setConfig(__DIR__ . '/conf/db.ini');
    $pdo = Connexion::makeConnection();
} catch (\Throwable $exception) {
    // Détail technique dans le journal, pas dans la réponse HTTP.
    error_log('Connexion base Touiteur : ' . $exception->getMessage());
    http_response_code(500);
    exit('Erreur de connexion à la base de données.');
}

$dispatcher = new Dispatcher($pdo);
$dispatcher->addRoute('default', HomeAction::class)
    ->addRoute('home', HomeAction::class)
    ->addRoute('mur', HomeAction::class)
    ->addRoute('detail', DetailAction::class)
    ->addRoute('user', UserAction::class)
    ->addRoute('profil', UserAction::class)
    ->addRoute('tag', TagAction::class)
    ->addRoute('formulaire', FormulaireAction::class)
    ->addRoute('follow', FollowAction::class)
    ->addRoute('like', LikeAction::class)
    ->addRoute('connexion', CompteConnex::class)
    ->addRoute('deconnexion', CompteConnex::class);

$dispatcher->run();
