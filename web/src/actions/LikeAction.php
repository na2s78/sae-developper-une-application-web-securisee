<?php

//Fonctionnalité 7 : Évaluer un touite

namespace touiteur\actions;

use GestionTouite;
use PDO;

class LikeAction extends Action
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function execute(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['idUtil'])) {
            header('Location: ?action=connexion');
            exit();
        }

        $idTouite = isset($_POST['idTouite']) ? (int)$_POST['idTouite'] : (isset($_GET['idTouite']) ? (int)$_GET['idTouite'] : 0);
        $valeur = isset($_POST['valeur']) ? (int)$_POST['valeur'] : (isset($_GET['valeur']) ? (int)$_GET['valeur'] : 0);
        $idUtil = (int)$_SESSION['idUtil'];

        if ($idTouite > 0 && ($valeur === 1 || $valeur === -1)) {
            $gestionTouite = new GestionTouite($this->pdo);
            $gestionTouite->evaluerTouite($idTouite, $idUtil, $valeur);
        }

        // Redirection vers la page précédente
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '?action=home';
        header("Location: $redirectUrl");
        exit();
    }
}
