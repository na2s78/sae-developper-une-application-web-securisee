<?php
// Fonctionnalité 7 : noter un touite avec un like (1) ou un dislike (-1). On ne va pas en dessous de zéro. Du moins pas encore.
namespace touiteur\actions;

use Throwable;

final class LikeAction extends Action
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

        $idTouite = filter_input(INPUT_POST, 'idTouite', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        $valeur = filter_input(INPUT_POST, 'valeur', FILTER_VALIDATE_INT);

        if ($idTouite === false || $idTouite === null || !in_array($valeur, [1, -1], true)) {
            $this->redirect($this->urlRetour(null));
        }

        try {
            $this->pdo->beginTransaction();
            $verrou = $this->pdo->prepare(
                'SELECT idTouite FROM touite WHERE idTouite = :idTouite FOR UPDATE'
            );
            $verrou->execute([':idTouite' => $idTouite]);
            if ($verrou->fetchColumn() === false) {
                $this->pdo->rollBack();
                $this->redirect('?action=home');
            }

            $stmt = $this->pdo->prepare(
                'SELECT valeur FROM evaluation WHERE idUtil = :idUtil AND idTouite = :idTouite'
            );
            $stmt->execute([':idUtil' => $idUtil, ':idTouite' => $idTouite]);
            $noteActuelle = $stmt->fetchColumn();
            $variationScore = 0;

            if ($noteActuelle === false) {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO evaluation (idUtil, idTouite, valeur)
                     VALUES (:idUtil, :idTouite, :valeur)'
                );
                $stmt->execute([
                    ':idUtil' => $idUtil,
                    ':idTouite' => $idTouite,
                    ':valeur' => $valeur,
                ]);
                $variationScore = $valeur;
            } elseif ((int) $noteActuelle === $valeur) {
                $stmt = $this->pdo->prepare(
                    'DELETE FROM evaluation WHERE idUtil = :idUtil AND idTouite = :idTouite'
                );
                $stmt->execute([':idUtil' => $idUtil, ':idTouite' => $idTouite]);
                $variationScore = -$valeur;
            } else {
                $stmt = $this->pdo->prepare(
                    'UPDATE evaluation SET valeur = :valeur
                     WHERE idUtil = :idUtil AND idTouite = :idTouite'
                );
                $stmt->execute([
                    ':valeur' => $valeur,
                    ':idUtil' => $idUtil,
                    ':idTouite' => $idTouite,
                ]);
                $variationScore = $valeur * 2;
            }

            $stmt = $this->pdo->prepare(
                'UPDATE touite
                 SET nbEval = GREATEST(nbEval + :variationScore, 0)
                 WHERE idTouite = :idTouite'
            );
            $stmt->execute([
                ':variationScore' => $variationScore,
                ':idTouite' => $idTouite,
            ]);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('LikeAction : ' . $exception->getMessage());
        }

        $this->redirect($this->urlRetour($idTouite));
    }

    private function urlRetour(?int $idTouite): string
    {
        $candidat = trim((string) ($_POST['retour'] ?? ''));
        if ($candidat !== '' && preg_match('/[\r\n]/', $candidat) !== 1) {
            if ($candidat[0] === '?'
                && preg_match('/^\?action=[a-z]+(&[a-zA-Z0-9_=%+.,-]*)?$/', $candidat) === 1) {
                return $candidat;
            }
            $parts = parse_url($candidat);
            if ($parts !== false) {
                $hostValide = !isset($parts['host']);
                if (isset($parts['host'])) {
                    $host = $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
                    $hostValide = hash_equals((string) ($_SERVER['HTTP_HOST'] ?? ''), $host);
                }
                $path = (string) ($parts['path'] ?? '');
                if ($hostValide
                    && $path !== ''
                    && $path[0] === '/'
                    && !str_starts_with($path, '//')
                    && !str_contains($path, '..')
                    && !str_contains($path, '\\')
                    && stripos($path, '%2f') === false
                    && stripos($path, '%5c') === false) {
                    return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
                }
            }
        }

        return $idTouite !== null ? '?action=detail&id=' . $idTouite : '?action=home';
    }
}
