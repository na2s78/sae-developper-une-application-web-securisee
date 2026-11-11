<?php
namespace touiteur\actions;

use PDO;

abstract class Action
{
    protected PDO $pdo;
    private array $teintesLogo = [];

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

    /** Echappe un texte puis transforme ses hashtags valides en liens internes. */
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
                return $match[1] . '<a class="tag" href="?action=tag&amp;tag=' . rawurlencode($tag) . '">#' . $this->e($tag) . '</a>';
            },
            $texte
        );
    }

    /** Retourne un extrait sans couper le dernier mot. */
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
        return $extrait . '…';
    }

    /** Le logo personnel reste bleu, les autres reçoivent une teinte aléatoire par page. */
    protected function logoAttribut(int $idUtil): string
    {
        $idConnecte = isset($_SESSION['idUtil']) ? (int) $_SESSION['idUtil'] : 0;
        if ($idConnecte === $idUtil) {
            return 'class="logo-touite"';
        }
        if (!isset($this->teintesLogo[$idUtil])) {
            $this->teintesLogo[$idUtil] = random_int(25, 335);
        }
        return 'class="logo-touite logo-autre" style="--logo-teinte:' . $this->teintesLogo[$idUtil] . 'deg"';
    }

    /**
     * Contrôles purement visuels : aucun formulaire n'est envoyé tant que
     * LikeAction n'est pas implémentée. Les cases permettent toutefois de
     * conserver l'état cliqué pendant l'affichage de la page.
     */
    protected function reactions(int $idTouite, int $score): string
    {
        $id = (int) $idTouite;
        return '<div class="reactions" aria-label="Évaluer ce touite">'
            . '<input class="reaction-toggle" type="radio" name="reaction-' . $id . '" value="like" id="like-' . $id . '">'
            . '<label class="reaction reaction-like" for="like-' . $id . '" title="J’aime" aria-label="J’aime"></label>'
            . '<input class="reaction-toggle" type="radio" name="reaction-' . $id . '" value="dislike" id="dislike-' . $id . '">'
            . '<label class="reaction reaction-dislike" for="dislike-' . $id . '" title="Je n’aime pas" aria-label="Je n’aime pas"></label>'
            . '<span class="score">' . (int) $score . '</span>'
            . '</div>';
    }

    /** Affiche le suivi uniquement pour un autre utilisateur. */
    protected function boutonSuivre(int $idUtil): string
    {
        if (!isset($_SESSION['idUtil']) || (int) $_SESSION['idUtil'] === $idUtil) {
            return '';
        }
        return '<form class="suivre-form" method="post" action="?action=follow">'
            . '<input type="hidden" name="type" value="user">'
            . '<input type="hidden" name="idUtilSuivi" value="' . $idUtil . '">'
            . '<button class="bouton bouton-suivre" type="submit">Suivre</button>'
            . '</form>';
    }

    protected function redirect(string $url = '?action=home'): void
    {
        header('Location: ' . $url, true, 303);
        exit;
    }
}
