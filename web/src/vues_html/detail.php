<?php if ($touite === null): ?>
    <section class="carte"><h1>Touite introuvable</h1><p>Ce touite n’existe pas ou n’est plus disponible.</p><a class="bouton" href="?action=home">Retour à l’accueil</a></section>
<?php else: ?>
    <article class="carte touite detail">
        <header class="touite-haut"><img <?= $this->logoAttribut((int) $touite['idUtil']) ?> src="ressources/images_css/touiteur_logo.svg" alt=""><div class="identite-auteur"><div class="nom-et-suivi"><a class="auteur" href="?action=user&amp;id=<?= (int) $touite['idUtil'] ?>"><?= $this->e($touite['prenomUtil'] . ' ' . $touite['nomUtil']) ?></a><?= $this->boutonSuivre((int) $touite['idUtil']) ?></div><time class="date" datetime="<?= $this->e($touite['datePub']) ?>"><?= $this->e($touite['datePub']) ?></time></div></header>
        <p class="touite-texte detail-texte"><?= $this->rendreTexte((string) $touite['texteTouite']) ?></p>
        <?php if (!empty($touite['cheminImage'])): ?><img class="touite-image" src="<?= $this->e($touite['cheminImage']) ?>" alt="<?= $this->e($touite['descImage'] ?? 'Image du touite') ?>"><?php endif; ?>
        <footer class="touite-bas"><?= $this->reactions((int) $touite['idTouite'], (int) $touite['nbEval']) ?><span class="date">Touite n°<?= (int) $touite['idTouite'] ?></span><a class="bouton bouton-contour" href="?action=home">Retour</a></footer>
    </article>
<?php endif; ?>
