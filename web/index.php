<?php

declare(strict_types=1);

require_once __DIR__ . '/src/GestionTouite.php';
require_once __DIR__ . '/src/actions/Action.php';
require_once __DIR__ . '/src/actions/HomeAction.php';
require_once __DIR__ . '/src/actions/DetailAction.php';
require_once __DIR__ . '/src/Dispatcher.php';

use touiteur\Dispatcher;

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=touiteur;charset=utf8mb4',
        'root',
        ''
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur de connexion BDD : ' . $e->getMessage());
}

$action = $_GET['action'] ?? 'home';

$dispatcher = new Dispatcher($pdo);
echo $dispatcher->dispatch($action);