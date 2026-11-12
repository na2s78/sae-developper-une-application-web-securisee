<?php
namespace touiteur\actions;

use touiteur\GestionTouite;

class MurAction extends Action
{
    public function execute(): string
    {
        $idUtil = (int) ($_SESSION['idUtil'] ?? 0);
        if ($idUtil <= 0) {
            $this->redirect('?action=connexion');
        }

        $gestion = new GestionTouite($this->pdo);
        return $this->renderView('home', [
            'touites' => $gestion->getMurUtilisateur($idUtil),
            'titre' => 'Mon mur',
        ]);
    }
}