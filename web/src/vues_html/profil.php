<?php if ($utilisateur === null): ?>
    <section class="carte"><h1>Utilisateur introuvable</h1><p>Ce profil n’existe pas.</p><a class="bouton" href="?action=home">Retour à l’accueil</a></section>
<?php else: ?>
    <section class="carte profil-entete"><h1><?= $this->e($utilisateur['prenomUtil'] . ' ' . $utilisateur['nomUtil']) ?></h1><p><?= $this->e($utilisateur['emailUtil']) ?></p><p class="date"><?= (int) $utilisateur['nbTouites'] ?> touite(s) publié(s)</p></section>
    <h2 class="titre-section">Ses touites</h2>
    <?php if ($touites === []): ?><p class="carte">Aucun touite publié.</p><?php endif; ?>
    <?php foreach ($touites as $touite): ?>
        <article class="carte touite"><header class="touite-haut"><img class="logo-touite" src="ressources/images_css/touiteur_logo.svg" alt=""><time class="date" datetime="<?= $this->e($touite['datePub']) ?>"><?= $this->e($touite['datePub']) ?></time></header><p class="touite-texte"><?= nl2br($this->e($touite['texteTouite']), false) ?></p><a class="lien-detail" href="?action=detail&amp;id=<?= (int) $touite['idTouite'] ?>">Voir le détail →</a></article>
    <?php endforeach; ?>
<?php endif; ?>
