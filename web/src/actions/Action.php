<?php
namespace touiteur\actions;

use PDO;

abstract class Action
{
    protected PDO $pdo;
    private array $teintesLogo = [];
    private const COULEURS_LOGO = [
        'rose', 'rouge', 'orange', 'jaune', 'vert-clair',
        'vert-fonce', 'bleu-fonce', 'violet', 'marron',
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    abstract public function execute(): string;

    protected function renderView(string $name, array $data = []): string
    {
        $view = __DIR__ . '/../vues_html/' . $name . '.php';
        if (!is_file($view)) {
            throw new \RuntimeException('Vue introuvable.');
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $view;
        return (string) ob_get_clean();
    }

    protected function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected function rendreTexte(string $texte): string
    {
        $texte = nl2br($this->e($texte), false);
        return (string) preg_replace_callback(
            \touiteur\GestionTouite::REGEX_TAG,
            function (array $match): string {
                $tag = $match[2];
                if (mb_strlen($tag) > 30) {
                    return $match[0];
                }
                return $match[1]
                    . '<a class="tag" href="?action=tag&amp;tag=' . rawurlencode($tag) . '">#'
                    . $this->e($tag) . '</a>';
            },
            $texte
        );
    }

    protected function extraitTexte(string $texte, int $max = 100): string
    {
        if (mb_strlen($texte) <= $max) {
            return $texte;
        }
        $extrait = mb_substr($texte, 0, $max);
        $dernierEspace = mb_strrpos($extrait, ' ');
        if ($dernierEspace !== false) {
            $extrait = mb_substr($extrait, 0, $dernierEspace);
        }
        return $extrait . '...';
    }

    // Le logo personnel reste bleu (noir admin), les autres reçoivent une autre couleur
    protected function logoAttribut(int $idUtil, int $roleUtil = 1): string
    {
        $idConnecte = isset($_SESSION['idUtil']) ? (int) $_SESSION['idUtil'] : 0;
        if ($roleUtil === 100) {
            return 'class="logo-touite logo-admin"';
        }
        if ($idConnecte === $idUtil) {
            return 'class="logo-touite"';
        }
        if (!isset($this->teintesLogo[$idUtil])) {
            $this->teintesLogo[$idUtil] = self::COULEURS_LOGO[random_int(0, count(self::COULEURS_LOGO) - 1)];
        }
        return 'class="logo-touite logo-autre logo-' . $this->teintesLogo[$idUtil] . '"';
    }

    protected function mentionAuteur(int $roleUtil): string
    {
        return $roleUtil === 100 ? ' - Admin' : '';
    }

    protected function reactions(int $idTouite, int $score): string
    {
        $id = (int) $idTouite;
        $vote = null;
        $idConnecte = (int) ($_SESSION['idUtil'] ?? 0);
        if ($idConnecte > 0) {
            $stmt = $this->pdo->prepare(
                'SELECT valeur FROM evaluation WHERE idUtil = :idUtil AND idTouite = :idTouite'
            );
            $stmt->execute([':idUtil' => $idConnecte, ':idTouite' => $id]);
            $valeur = $stmt->fetchColumn();
            $vote = $valeur === false ? null : (int) $valeur;
        }

        $retour = $this->e($_SERVER['REQUEST_URI'] ?? '?action=home');
        $bouton = function (int $valeur, string $classe, string $titre) use ($id, $vote, $retour): string {
            $actif = $vote === $valeur;
            return '<form class="reaction-form" method="post" action="?action=like">'
                . '<input type="hidden" name="idTouite" value="' . $id . '">'
                . '<input type="hidden" name="valeur" value="' . $valeur . '">'
                . '<input type="hidden" name="retour" value="' . $retour . '">'
                . '<button class="reaction ' . $classe . ($actif ? ' actif' : '') . '" type="submit"'
                . ' title="' . $titre . '" aria-label="' . $titre . '" aria-pressed="'
                . ($actif ? 'true' : 'false') . '"></button></form>';
        };

        return '<div class="reactions" aria-label="Évaluer ce touite">'
            . $bouton(1, 'reaction-like', 'J’aime')
            . $bouton(-1, 'reaction-dislike', 'Je n’aime pas')
            . '<span class="score" title="Nombre de likes">' . (int) $score . '</span>'
            . '</div>';
    }

    protected function boutonSuivre(int $idUtil): string
    {
        $idConnecte = (int) ($_SESSION['idUtil'] ?? 0);
        if ($idConnecte <= 0 || $idConnecte === $idUtil) {
            return '';
        }

        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM suivre WHERE idSuiveur = :idSuiveur AND idSuivi = :idSuivi'
        );
        $stmt->execute([':idSuiveur' => $idConnecte, ':idSuivi' => $idUtil]);
        $suit = $stmt->fetchColumn() !== false;

        return '<form class="suivre-form" method="post" action="?action=follow">'
            . '<input type="hidden" name="type" value="user">'
            . '<input type="hidden" name="idUtilSuivi" value="' . $idUtil . '">'
            . '<input type="hidden" name="mode" value="' . ($suit ? 'neplus' : 'suivre') . '">'
            . '<input type="hidden" name="retour" value="'
            . $this->e($_SERVER['REQUEST_URI'] ?? '?action=home') . '">'
            . '<button class="bouton bouton-suivre' . ($suit ? ' bouton-contour' : '') . '" type="submit">'
            . ($suit ? 'Ne plus suivre' : 'Suivre') . '</button></form>';
    }

    protected function redirect(string $url = '?action=home'): void
    {
        header('Location: ' . $url, true, 303);
        exit;
    }
}
