<?php
// Fonctionnalité 4 : afficher les touites d'un utilisateur donné
namespace touiteur\actions;

use touiteur\GestionTouite;

class UserAction extends Action
{
    public function execute(): string
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        // "Mon profil" (sans id dans l'URL) : on prend l'utilisateur connecté
        if (!$id && isset($_SESSION['idUtil'])) {
            $id = (int) $_SESSION['idUtil'];
        }

        $utilisateur = null;
        $touites = [];
        if ($id) {
            $gestion = new GestionTouite($this->pdo);
            $utilisateur = $gestion->getUtilisateurById($id);
            if ($utilisateur !== null) {
                $touites = $gestion->getTouitesParUtilisateur($id);
            }
        }

        if ($utilisateur === null) {
            http_response_code(404);
        }

        return $this->renderView('profil', ['utilisateur' => $utilisateur, 'touites' => $touites]);
    }
}
