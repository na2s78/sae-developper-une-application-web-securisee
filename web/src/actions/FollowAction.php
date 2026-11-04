<?php

//Fonctionnalités 10 et 11 combinées

namespace touiteur\actions;

use GestionTouite;
use PDO;

class FollowAction extends Action
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

        $idUtilConnecte = (int)$_SESSION['idUtil'];
        $gestionTouite = new GestionTouite($this->pdo);

        $type = $_POST['type'] ?? $_GET['type'] ?? '';

        // Fonctionnalité 10 : Suivre / Ne plus suivre un utilisateur

        if ($type === 'user') {
            $idUtilSuivi = isset($_POST['idUtilSuivi']) ? (int)$_POST['idUtilSuivi'] : 0;
            $mode = $_POST['mode'] ?? 'suivre'; // 'suivre' ou 'stopper'

            if ($idUtilSuivi > 0) {
                if ($mode === 'suivre') {
                    $gestionTouite->suivreUtilisateur($idUtilConnecte, $idUtilSuivi);
                } else {
                    $gestionTouite->nePlusSuivreUtilisateur($idUtilConnecte, $idUtilSuivi);
                }
            }
        }

        // Fonctionnalité 11 : S'abonner / Se désabonner d'un tag

        elseif ($type === 'tag') {
            $idTag = isset($_POST['idTag']) ? (int)$_POST['idTag'] : 0;
            $mode = $_POST['mode'] ?? 'abonner'; // 'abonner' ou 'desabonner'

            if ($idTag > 0) {
                if ($mode === 'abonner') {
                    $gestionTouite->abonnerTag($idUtilConnecte, $idTag);
                } else {
                    $gestionTouite->desabonnerTag($idUtilConnecte, $idTag);
                }
            }
        }

        $redirectUrl = $_POST['redirect'] ?? $_GET['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? '?action=home';
        header("Location: $redirectUrl");
        exit();
    }
}