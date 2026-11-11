<?php
$message = $message ?? null;
$titre = $titre ?? 'Derniers touites';
$touites = $touites ?? [];
$similaires = $similaires ?? [];
$rechercheTag = $rechercheTag ?? false;
?>
<section class="carte publier">
    <h1><?= $this->e($titre) ?></h1>
    <?php if ($rechercheTag && $message === null): ?><p class="date"><?= count($touites) ?> touite(s) avec ce tag</p><?php endif; ?>
    <?php if ($message !== null): ?><p class="alerte"><?= $this->e($message) ?></p><?php endif; ?>
    <form method="post" action="?action=formulaire" enctype="multipart/form-data">
        <label class="visuellement-cache" for="texteTouite">Votre touite</label>
        <textarea id="texteTouite" name="texteTouite" maxlength="235" placeholder="Quoi de neuf ? Ajoutez des #tags..." required></textarea>
        <div class="publier-bas"><label class="bouton-image" for="image">Ajouter une image<input id="image" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp"></label><button class="bouton" type="submit">Touiter</button></div>
    </form>
</section>
<?php if ($rechercheTag && $touites === [] && $message === null): ?>
    <section class="carte">
        <p>Aucun touite avec ce tag.</p>
        <?php if ($similaires !== []): ?>
            <p>Vous cherchiez peut-être :
                <?php foreach ($similaires as $similaire): ?><a class="tag" href="?action=tag&amp;tag=<?= rawurlencode($similaire) ?>">#<?= $this->e($similaire) ?></a><?php endforeach; ?>
            </p>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php if (!$rechercheTag && $touites === []): ?>
    <p class="carte">Aucun touite à afficher.</p>
<?php else: ?>
    <?php foreach ($touites as $touite): ?>
        <?php $texteCourt = $this->extraitTexte((string) $touite['texteTouite']); ?>
        <article class="carte touite">
            <header class="touite-haut"><img <?= $this->logoAttribut((int) $touite['idUtil']) ?> src="ressources/images_css/touiteur_logo.svg" alt=""><div class="identite-auteur"><div class="nom-et-suivi"><a class="auteur" href="?action=user&amp;id=<?= (int) $touite['idUtil'] ?>"><?= $this->e($touite['prenomUtil'] . ' ' . $touite['nomUtil']) ?></a><?= $this->boutonSuivre((int) $touite['idUtil']) ?></div><time class="date" datetime="<?= $this->e($touite['datePub']) ?>"><?= $this->e($touite['datePub']) ?></time></div></header>
            <p class="touite-texte"><?= $this->rendreTexte($texteCourt) ?></p>
            <footer class="touite-bas"><span class="date">Publié par <?= $this->e($touite['prenomUtil'] . ' ' . $touite['nomUtil']) ?></span><?= $this->reactions((int) $touite['idTouite'], (int) $touite['nbEval']) ?><a class="lien-detail" href="?action=detail&amp;id=<?= (int) $touite['idTouite'] ?>">Voir le détail →</a></footer>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
