<?php
namespace touiteur\actions;

class HomeAction extends Action
{
    public function execute(): string
    {
        $gestion = new \touiteur\GestionTouite($this->pdo);
        return $this->renderView('home', [
            'touites' => $gestion->getTousLesTouites(),
            'titre' => 'Derniers touites',
        ]);
    }
}
