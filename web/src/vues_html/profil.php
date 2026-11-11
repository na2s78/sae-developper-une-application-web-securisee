<?php if ($utilisateur === null): ?>
    <section class="carte"><h1>Utilisateur introuvable</h1><p>Ce profil n’existe pas.</p><a class="bouton" href="?action=home">Retour à l’accueil</a></section>
<?php else: ?>
    <section class="carte profil-entete">
        <h1><?= $this->e($utilisateur['prenomUtil'] . ' ' . $utilisateur['nomUtil']) ?></h1>
        <p class="date"><?= count($touites) ?> touite(s) publié(s)</p>
    </section>
    <h2 class="titre-section">Ses touites</h2>
    <?php if ($touites === []): ?>
        <p class="carte">Aucun touite publié.</p>
    <?php else: ?>
        <?= $this->renderView('liste_touites', ['touites' => $touites, 'afficherAuteur' => false]) ?>
    <?php endif; ?>
<?php endif; ?>
