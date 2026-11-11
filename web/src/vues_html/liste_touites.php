<?php
// Liste de touites en version courte (fonctionnalité 1)
// Pas d'image ici : elle n'apparaît qu'en détail
use touiteur\TexteTouite;

$afficherAuteur = $afficherAuteur ?? true;
?>
<?php foreach ($touites as $touite): ?>
    <article class="carte touite">
        <header class="touite-haut">
            <img class="logo-touite" src="ressources/images_css/touiteur_logo.svg" alt="">
            <div>
                <?php if ($afficherAuteur): ?>
                    <a class="auteur" href="?action=user&amp;id=<?= (int) $touite['idUtil'] ?>"><?= TexteTouite::e($touite['prenomUtil'] . ' ' . $touite['nomUtil']) ?></a>
                <?php endif; ?>
                <time class="date" datetime="<?= TexteTouite::e($touite['datePub']) ?>"><?= TexteTouite::e($touite['datePub']) ?></time>
            </div>
        </header>
        <p class="touite-texte"><?= TexteTouite::rendre(TexteTouite::extrait((string) $touite['texteTouite'])) ?></p>
        <footer class="touite-bas">
            <span class="date">Score : <?= (int) $touite['nbEval'] ?></span>
            <a class="lien-detail" href="?action=detail&amp;id=<?= (int) $touite['idTouite'] ?>">Voir le détail →</a>
        </footer>
    </article>
<?php endforeach; ?>
