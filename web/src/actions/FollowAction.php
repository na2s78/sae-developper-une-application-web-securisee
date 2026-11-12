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
    }}
