<?php
// Fonctionnalités 10 et 11 : suivre un utilisateur et s'abonner à un tag.
namespace touiteur\actions;

use PDOException;
use Throwable;
use touiteur\GestionTouite;

class FollowAction extends Action
{
    public function execute(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('?action=home');
        }

        $idUtil = (int) ($_SESSION['idUtil'] ?? 0);
        if ($idUtil <= 0) {
            $this->redirect('?action=connexion');
        }

        $gestion = new GestionTouite($this->pdo);
        $type = (string) ($_POST['type'] ?? '');
        $mode = (string) ($_POST['mode'] ?? '');

        try {
            if ($type === 'user') {
                $this->traiterUtilisateur($gestion, $idUtil, $mode);
            } elseif ($type === 'tag') {
                $this->traiterTag($gestion, $idUtil, $mode);
            }
        } catch (Throwable $exception) {
            // Les FK et contraintes uniques restent la dernière défense en base.
            error_log('FollowAction : ' . $exception->getMessage());
        }

        $this->redirect($this->urlRetour());
    }
    /** Fonctionnalité 10 : suivre ou ne plus suivre un utilisateur. */
    private function traiterUtilisateur(GestionTouite $gestion, int $idUtil, string $mode): void
    {
        $idSuivi = filter_input(INPUT_POST, 'idUtilSuivi', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if ($idSuivi === false || $idSuivi === null || $idSuivi === $idUtil) {
            return;
        }
        if ($gestion->getUtilisateurById($idSuivi) === null) {
            return;
        }

        if (in_array($mode, ['neplus', 'stopper', 'desabonner'], true)) {
            $gestion->nePlusSuivreUtilisateur($idUtil, $idSuivi);
        } else {
            // INSERT IGNORE dans GestionTouite rend l'action idempotente.
            $gestion->suivreUtilisateur($idUtil, $idSuivi);
        }
    }
}
