<?php
$action = $this;
$renderText = static function (string $texte) use ($action): string {
    $safe = nl2br($action->e($texte), false);
    return (string) preg_replace_callback('/(^|[\s])#([\p{L}\p{N}_-]+)/u', static function (array $match) use ($action): string {
        return $match[1] . '<a class="tag" href="?action=tag&amp;tag=' . rawurlencode($match[2]) . '">#' . $action->e($match[2]) . '</a>';
    }, $safe);
};
$message = $message ?? null;
$titre = $titre ?? 'Derniers touites';
$touites = $touites ?? [];
?>
<section class="carte publier">
    <h1><?= $this->e($titre) ?></h1>
    <?php if ($message !== null): ?><p class="alerte"><?= $this->e($message) ?></p><?php endif; ?>
    <form method="post" action="?action=formulaire" enctype="multipart/form-data">
        <label class="visuellement-cache" for="texteTouite">Votre touite</label>
        <textarea id="texteTouite" name="texteTouite" maxlength="235" placeholder="Quoi de neuf ? Ajoutez des #tags..." required></textarea>
        <div class="publier-bas"><label class="bouton-image" for="image">Ajouter une image<input id="image" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp"></label><button class="bouton" type="submit">Touiter</button></div>
    </form>
</section>
<?php if ($touites === []): ?>
    <p class="carte">Aucun touite à afficher.</p>
<?php else: ?>
    <?php foreach ($touites as $touite): ?>
        <article class="carte touite">
            <header class="touite-haut"><img class="logo-touite" src="ressources/images_css/touiteur_logo.svg" alt=""><div><a class="auteur" href="?action=user&amp;id=<?= (int) $touite['idUtil'] ?>"><?= $this->e(($touite['prenomUtil'] ?? '') . ' ' . ($touite['nomUtil'] ?? '')) ?></a><time class="date" datetime="<?= $this->e($touite['datePub'] ?? '') ?>"><?= $this->e($touite['datePub'] ?? '') ?></time></div></header>
            <p class="touite-texte"><?= $renderText((string) ($touite['texteTouite'] ?? '')) ?></p>
            <?php if (!empty($touite['cheminImage'])): ?><img class="touite-image" src="<?= $this->e($touite['cheminImage']) ?>" alt="Image associée au touite"><?php endif; ?>
            <footer class="touite-bas"><span class="date">Publié par <?= $this->e(($touite['prenomUtil'] ?? '') . ' ' . ($touite['nomUtil'] ?? '')) ?></span><a class="lien-detail" href="?action=detail&amp;id=<?= (int) $touite['idTouite'] ?>">Voir le détail →</a></footer>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
