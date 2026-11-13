<?php
$message = $message ?? null;
$titre = $titre ?? 'Derniers touites';
$touites = $touites ?? [];
$similaires = $similaires ?? [];
$rechercheTag = $rechercheTag ?? false;
$rechercheUtilisateur = $rechercheUtilisateur ?? false;
$rechercheTexte = $rechercheTexte ?? false;
$utilisateurs = $utilisateurs ?? [];
$tagRecherche = $tagRecherche ?? null;
?>
<section class="carte publier">
    <h1><?= $this->e($titre) ?></h1>
    <?php if ($rechercheTag && $message === null): ?><p class="date"><?= count($touites) ?> touite(s) avec ce tag</p><?php endif; ?>
    <?php if ($message !== null): ?><p class="alerte"><?= $this->e($message) ?></p><?php endif; ?>
    <form method="post" action="?action=formulaire" enctype="multipart/form-data">
        <input type="hidden" name="retour" value="<?= ($_GET['action'] ?? '') === 'mur' ? 'mur' : 'home' ?>">
        <label class="visuellement-cache" for="texteTouite">Votre touite</label>
        <textarea id="texteTouite" name="texteTouite" maxlength="235" placeholder="Quoi de neuf ? Ajoutez des #tags..." required></textarea>
        <div class="publier-bas"><label class="bouton-image" for="image">Ajouter une image<input id="image" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp"></label><button class="bouton" type="submit">Touiter</button></div>
        <img id="apercu-image-home" class="apercu-image" alt="" hidden>
    </form>
</section>
<script>
document.getElementById('image')?.addEventListener('change', function () {
    const apercu = document.getElementById('apercu-image-home');
    const fichier = this.files && this.files[0];
    if (!fichier || !fichier.type.startsWith('image/')) {
        apercu.hidden = true;
        apercu.removeAttribute('src');
        return;
    }
    apercu.src = URL.createObjectURL(fichier);
    apercu.hidden = false;
});
</script>
<?php if ($tagRecherche !== null): ?>
    <section class="carte resultat-tag">
        <span>Tag recherché : <a class="tag" href="?action=tag&amp;tag=<?= rawurlencode($tagRecherche['libelleTag']) ?>">#<?= $this->e($tagRecherche['libelleTag']) ?></a></span>
        <?php if ((int) ($_SESSION['idUtil'] ?? 0) > 0): ?>
            <form method="post" action="?action=follow" class="suivre-form">
                <input type="hidden" name="type" value="tag">
                <input type="hidden" name="idTag" value="<?= (int) $tagRecherche['idTag'] ?>">
                <input type="hidden" name="retour" value="<?= $this->e($_SERVER['REQUEST_URI'] ?? '?action=home') ?>">
                <button type="submit" class="bouton bouton-suivre">Suivre ce tag</button>
            </form>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php if ($rechercheUtilisateur): ?>
    <section class="carte resultats-utilisateurs">
        <h2>Utilisateurs trouvés</h2>
        <?php if ($utilisateurs === []): ?><p>Aucun utilisateur trouvé.</p><?php endif; ?>
        <?php foreach ($utilisateurs as $utilisateur): ?>
            <div class="resultat-utilisateur"><img <?= $this->logoAttribut((int) $utilisateur['idUtil'], (int) $utilisateur['roleUtil']) ?> src="ressources/images_css/touiteur_logo.svg" alt=""><a class="auteur" href="?action=user&amp;id=<?= (int) $utilisateur['idUtil'] ?>"><?= $this->e($utilisateur['prenomUtil'] . ' ' . $utilisateur['nomUtil']) ?></a><?= $this->boutonSuivre((int) $utilisateur['idUtil']) ?></div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
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
<?php if ($rechercheTexte && $touites === [] && $message === null): ?>
    <p class="carte">Aucun touite trouvé.</p>
<?php elseif (!$rechercheTag && !$rechercheTexte && !$rechercheUtilisateur && $touites === []): ?>
    <p class="carte">Aucun touite à afficher.</p>
<?php else: ?>
    <?php foreach ($touites as $touite): ?>
        <?php $texteCourt = $this->extraitTexte((string) $touite['texteTouite']); ?>
        <article class="carte touite">
            <header class="touite-haut"><img <?= $this->logoAttribut((int) $touite['idUtil'], (int) $touite['roleUtil']) ?> src="ressources/images_css/touiteur_logo.svg" alt=""><div class="identite-auteur"><div class="nom-et-suivi"><a class="auteur" href="?action=user&amp;id=<?= (int) $touite['idUtil'] ?>"><?= $this->e($touite['prenomUtil'] . ' ' . $touite['nomUtil']) ?></a><?= $this->boutonSuivre((int) $touite['idUtil']) ?></div><time class="date" datetime="<?= $this->e($touite['datePub']) ?>"><?= $this->e($touite['datePub']) ?></time></div></header>
            <p class="touite-texte"><?= $this->rendreTexte($texteCourt) ?><?php if (!empty($touite['cheminImage'])): ?><img class="image-indicateur" src="ressources/images_css/img_logo.svg" alt="Image associée" title="Image associée"><?php endif; ?></p>
            <footer class="touite-bas"><span class="date">Publié par <?= $this->e($touite['prenomUtil'] . ' ' . $touite['nomUtil']) ?><?= $this->e($this->mentionAuteur((int) $touite['roleUtil'])) ?></span><?= $this->reactions((int) $touite['idTouite'], (int) $touite['nbEval']) ?><a class="lien-detail" href="?action=detail&amp;id=<?= (int) $touite['idTouite'] ?>">Voir le détail →</a></footer>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
