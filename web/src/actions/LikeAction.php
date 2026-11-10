<?php
// Fonctionnalité 7 : évaluer un touite.
namespace touiteur\actions;

use PDO;

final class LikeAction extends Action
{
    public function execute(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        if (!isset($_SESSION['idUtil'])) {
            $this->redirect('?action=connexion');
        }

        $idTouite = filter_input(INPUT_POST, 'idTouite', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        $valeur = filter_input(INPUT_POST, 'valeur', FILTER_VALIDATE_INT);

        if ($idTouite === false || $idTouite === null || !in_array($valeur, [1, -1], true)) {
            $this->redirect();
        }

        // La valeur est blanche-listée et l'identifiant est lié : aucune concaténation SQL.
        $sql = 'UPDATE Touite
                SET nbEval = CASE WHEN nbEval + ? < 0 THEN 0 ELSE nbEval + ? END
                WHERE idTouite = ?';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([$valeur, $valeur, $idTouite]);

        $this->redirect();
    }

    private function redirect(string $fallback = '?action=home'): void
    {
        $target = $fallback;
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        if ($referer !== '') {
            $parts = parse_url($referer);
            $sameHost = !isset($parts['host']) || $parts['host'] === ($_SERVER['HTTP_HOST'] ?? '');
            $path = $parts['path'] ?? '';
            if ($sameHost && $path !== '' && !preg_match('/[\r\n]/', $referer)) {
                $target = $path;
                if (isset($parts['query'])) {
                    $target .= '?' . $parts['query'];
                }
                if (isset($parts['fragment'])) {
                    $target .= '#' . $parts['fragment'];
                }
            }
        }

        header('Location: ' . $target, true, 303);
        exit;
    }
}
