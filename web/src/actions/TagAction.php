<?php
// Fonctionnalité 5 : afficher les touites associés à un tag (et recherche par tag)
namespace touiteur\actions;

use touiteur\GestionTouite;

class TagAction extends Action
{
    public function execute(): string
    {
        // "#Foot", "foot" et " foot " donnent tous "foot"
        $libelle = trim($_GET['tag'] ?? '');
        $libelle = mb_strtolower(ltrim($libelle, '#'));

        if ($libelle === '') {
            $this->redirect('?action=home');
        }

        // Un tag ne contient que des lettres, chiffres ou _ (30 caractères max)
        $valide = preg_match('/^[\p{L}\p{N}_]{1,30}$/u', $libelle) === 1;

        $touites = [];
        $similaires = [];
        if ($valide) {
            $gestion = new GestionTouite($this->pdo);
            $touites = $gestion->getTouitesParLibelleTag($libelle);
            if ($touites === []) {
                $similaires = $gestion->getTagsSimilaires($libelle);
            }
        }

        return $this->renderView('tag', [
            'libelle' => $libelle,
            'valide' => $valide,
            'touites' => $touites,
            'similaires' => $similaires,
        ]);
    }
}
