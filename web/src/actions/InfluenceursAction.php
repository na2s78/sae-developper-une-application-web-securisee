<?php
// Fonctionnalité 14 : liste des influenceurs, réservée aux administrateurs
namespace touiteur\actions;

use touiteur\GestionTouite;

class InfluenceursAction extends Action
{
    public function execute(): string
    {
        if (empty($_SESSION['idUtil'])) {
            $this->redirect('?action=connexion');
        }

        $gestion = new GestionTouite($this->pdo);

        // Le rôle est relu en base : on ne se fie pas à ce qui vient du navigateur
        $utilisateur = $gestion->getUtilisateurById((int) $_SESSION['idUtil']);
        if ($utilisateur === null || (int) $utilisateur['roleUtil'] !== 100) {
            http_response_code(403);
            return '<section class="carte"><h1>Accès refusé</h1>'
                . '<p>Cette page est réservée aux administrateurs.</p></section>';
        }

        $html = '<section class="carte"><h1>Top touiteurs</h1>'
            . '<p class="date">Utilisateurs classés par nombre de suiveurs (ordre croissant)</p>'
            . '<ol class="liste-tags">';

        foreach ($gestion->getInfluenceurs() as $influenceur) {
            $html .= '<li><a class="auteur" href="?action=user&amp;id=' . (int) $influenceur['idUtil'] . '">'
                . $this->e($influenceur['prenomUtil'] . ' ' . $influenceur['nomUtil']) . '</a>'
                . '<span>' . (int) $influenceur['nbSuiveurs'] . ' suiveur(s)</span></li>';
        }

        return $html . '</ol></section>';
    }
}
