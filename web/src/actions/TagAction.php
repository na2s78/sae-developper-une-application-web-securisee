<?php
// Fonctionnalité 5 : afficher les touites associés à un tag et les suggestions.
namespace touiteur\actions;

use touiteur\GestionTouite;

class TagAction extends Action
{
    public function execute(): string
    {
        $libelle = mb_strtolower(ltrim(trim((string) ($_GET['tag'] ?? '')), '#'));
        if ($libelle === '') {
            $this->redirect('?action=home');
        }
        $valide = preg_match('/^[\p{L}\p{N}_]{1,30}$/u', $libelle) === 1;
        $touites = [];
        $similaires = [];
        $tagRecherche = null;
        if ($valide) {
            $gestion = new GestionTouite($this->pdo);
            $touites = $gestion->getTouitesParLibelleTag($libelle);
            $tagRecherche = $gestion->getTagByLibelle($libelle);
            if ($touites === []) {
                $similaires = $gestion->getTagsSimilaires($libelle);
            }
        }
        return $this->renderView('home', [
            'titre' => '#' . $libelle,
            'message' => !$valide ? 'Un tag ne contient que des lettres, des chiffres et des _ (30 caractères maximum).' : null,
            'touites' => $touites,
            'similaires' => $similaires,
            'tagRecherche' => $tagRecherche,
            'rechercheTag' => true,
        ]);
    }
}
