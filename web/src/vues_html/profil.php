<?php if ($utilisateur === null): ?>
    <section class="carte"><h1>Utilisateur introuvable</h1><p>Ce profil n’existe pas.</p><a class="bouton" href="?action=home">Retour à l’accueil</a></section>
<?php else: ?>
    <section class="carte profil-entete"><h1><?= $this->e($utilisateur['prenomUtil'] . ' ' . $utilisateur['nomUtil']) ?></h1><p class="date"><?= count($touites) ?> touite(s) publié(s)</p></section>
    <h2 class="titre-section">Ses touites</h2>
    <?php if ($touites === []): ?><p class="carte">Aucun touite publié.</p><?php endif; ?>
    <?php foreach ($touites as $touite): ?>
        <?php $texteCourt = $this->extraitTexte((string) $touite['texteTouite']); ?>
        <article class="carte touite">
            <header class="touite-haut"><img <?= $this->logoAttribut((int) $touite['idUtil']) ?> src="ressources/images_css/touiteur_logo.svg" alt=""><time class="date" datetime="<?= $this->e($touite['datePub']) ?>"><?= $this->e($touite['datePub']) ?></time></header>
            <p class="touite-texte"><?= $this->rendreTexte($texteCourt) ?></p>
            <footer class="touite-bas"><?= $this->reactions((int) $touite['idTouite'], (int) $touite['nbEval']) ?><a class="lien-detail" href="?action=detail&amp;id=<?= (int) $touite['idTouite'] ?>">Voir le détail →</a></footer>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
