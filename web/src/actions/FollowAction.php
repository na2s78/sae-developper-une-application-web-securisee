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
    // Fonctionnalité 10 : suivre ou ne plus suivre un utilisateur
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

    // Fonctionnalité 11 : s'abonner ou se désabonner d'un tag.
    private function traiterTag(GestionTouite $gestion, int $idUtil, string $mode): void
    {
        $idTag = filter_input(INPUT_POST, 'idTag', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($idTag === false || $idTag === null) {
            // Le formulaire du Dispatcher envoie libelleTag (ex. « #php »).
            $libelle = mb_strtolower(ltrim(trim((string) ($_POST['libelleTag'] ?? '')), '#'));
            if (preg_match('/^[\p{L}\p{N}_]{1,30}$/u', $libelle) !== 1) {
                return;
            }

            $tag = $gestion->getTagByLibelle($libelle);
            if ($tag !== null) {
                $idTag = (int) $tag['idTag'];
            } else {
                // Création ici, sans ajouter de méthode à GestionTouite.php.
                try {
                    $insert = $this->pdo->prepare(
                        'INSERT INTO tag (libelleTag, descTag) VALUES (:libelle, NULL)'
                    );
                    $insert->execute([':libelle' => $libelle]);
                    $idTag = (int) $this->pdo->lastInsertId();
                } catch (PDOException $exception) {
                    $tag = $gestion->getTagByLibelle($libelle);
                    if ($tag === null) {
                        throw $exception;
                    }
                    $idTag = (int) $tag['idTag'];
                }
            }
        }

        if ($idTag === null || $idTag <= 0) {
            return;
        }
        if ($mode === 'desabonner') {
            $gestion->desabonnerTag($idUtil, $idTag);
        } else {
            // INSERT IGNORE rend l'abonnement idempotent.
            $gestion->abonnerTag($idUtil, $idTag);
        }
    }

    private function urlRetour(): string
    {
        foreach ([$_POST['retour'] ?? '', $_SERVER['HTTP_REFERER'] ?? ''] as $candidat) {
            $candidat = trim((string) $candidat);
            if ($candidat === '' || preg_match('/[\r\n]/', $candidat) === 1) {
                continue;
            }
            if ($candidat[0] === '?'
                && preg_match('/^\?action=[a-z]+(&[a-zA-Z0-9_=%+.,-]*)?$/', $candidat) === 1) {
                return $candidat;
            }

            $parts = parse_url($candidat);
            if ($parts === false) {
                continue;
            }
            if (isset($parts['host'])) {
                $host = $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
                if (!hash_equals((string) ($_SERVER['HTTP_HOST'] ?? ''), $host)) {
                    continue;
                }
            }
            $path = (string) ($parts['path'] ?? '');
            if ($path !== '' && $path[0] === '/' && !str_starts_with($path, '//')
                && !str_contains($path, '..') && !str_contains($path, '\\')
                && stripos($path, '%2f') === false && stripos($path, '%5c') === false) {
                return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
            }
        }
        return '?action=home';
    }
}
