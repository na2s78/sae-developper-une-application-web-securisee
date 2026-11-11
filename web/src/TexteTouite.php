<?php

namespace touiteur;

// Affichage du texte d'un touite (échappement, liens sur les tags, version courte)
class TexteTouite
{
    // Protège le texte contre les injections HTML (XSS)
    public static function e($valeur): string
    {
        return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
    }

    // Transforme chaque #tag du texte en lien vers la page du tag
    public static function rendre(string $texte): string
    {
        $texte = nl2br(self::e($texte));

        return (string) preg_replace_callback(GestionTouite::REGEX_TAG, function ($m) {
            $tag = $m[2];
            if (mb_strlen($tag) > 30) {
                return $m[0]; // trop long : ce n'est pas un tag enregistré, on n'en fait pas un lien
            }
            return $m[1] . '<a class="tag" href="?action=tag&amp;tag=' . rawurlencode($tag) . '">#' . $tag . '</a>';
        }, $texte);
    }

    // Version courte pour les listes : on coupe au dernier espace pour ne pas couper un mot
    public static function extrait(string $texte, int $max = 100): string
    {
        if (mb_strlen($texte) <= $max) {
            return $texte;
        }
        $debut = mb_substr($texte, 0, $max);
        $dernierEspace = mb_strrpos($debut, ' ');
        if ($dernierEspace !== false) {
            $debut = mb_substr($debut, 0, $dernierEspace);
        }
        return $debut . '…';
    }
}
