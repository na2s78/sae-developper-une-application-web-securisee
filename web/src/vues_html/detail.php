<?php if ($touite === null): ?>
    <section class="carte"><h1>Touite introuvable</h1><p>Ce touite n’existe pas ou n’est plus disponible.</p><a class="bouton" href="?action=home">Retour à l’accueil</a></section>
<?php else:
    $action = $this;
    $texte = nl2br($action->e($touite['texteTouite']), false);
    $texte = preg_replace_callback('/(^|[\s])#([\p{L}\p{N}_-]+)/u', static function (array $match) use ($action): string {
        return $match[1] . '<a class="tag" href="?action=tag&amp;tag=' . rawurlencode($match[2]) . '">#' . $action->e($match[2]) . '</a>';
    }, $texte);
?>
    <article class="carte touite detail">
        <header class="touite-haut"><img class="logo-touite" src="ressources/images_css/touiteur_logo.svg" alt=""><div><a class="auteur" href="?action=user&amp;id=<?= (int) $touite['idUtil'] ?>"><?= $this->e($touite['prenomUtil'] . ' ' . $touite['nomUtil']) ?></a><time class="date" datetime="<?= $this->e($touite['datePub']) ?>"><?= $this->e($touite['datePub']) ?></time></div></header>
        <p class="touite-texte detail-texte"><?= $texte ?></p>
        <?php if (!empty($touite['cheminImage'])): ?><img class="touite-image" src="<?= $this->e($touite['cheminImage']) ?>" alt="<?= $this->e($touite['descImage'] ?? 'Image du touite') ?>"><?php endif; ?>
        <footer class="touite-bas"><span class="date">Touite n°<?= (int) $touite['idTouite'] ?></span><a class="bouton bouton-contour" href="?action=home">Retour</a></footer>
    </article>
<?php endif; ?>
