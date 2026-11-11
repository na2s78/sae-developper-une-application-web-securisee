<?php

//Fonctionnalité 6 : Publier un touite

namespace touiteur\actions;

use touiteur\GestionTouite;
use PDO;

class FormulaireAction extends Action
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $texte = trim($_POST['texteTouite'] ?? '');
            $idUtil = (int)$_SESSION['idUtil'];

            if (!empty($texte) && mb_strlen($texte) <= 235) {
                $gestionTouite = new GestionTouite($this->pdo);
                $gestionTouite->publierTouite($idUtil, $texte);

                header('Location: ?action=home');
                exit();
            }
        }

        return '
            <form action="index.php?action=formulaire" method="post">
                <label for="texteTouite">Votre touite (max 235 caractères) :</label><br>
                <textarea name="texteTouite" id="texteTouite" maxlength="235" required></textarea><br>
                <button type="submit">Publier le touite</button>
            </form>
        ';
    }
}
