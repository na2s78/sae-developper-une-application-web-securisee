<?php
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
    <?= $this->renderView('liste_touites', ['touites' => $touites]) ?>
<?php endif; ?>
