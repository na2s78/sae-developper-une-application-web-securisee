<?php use touiteur\TexteTouite; ?>
<section class="carte profil-entete">
    <h1>#<?= $this->e($libelle) ?></h1>
    <?php if ($valide): ?><p class="date"><?= count($touites) ?> touite(s) avec ce tag</p><?php endif; ?>
</section>
<?php if (!$valide): ?>
    <p class="alerte">Un tag ne contient que des lettres, des chiffres et des _ (30 caractères maximum).</p>
<?php elseif ($touites === []): ?>
    <section class="carte">
        <p>Aucun touite avec le tag #<?= $this->e($libelle) ?>.</p>
        <?php if ($similaires !== []): ?>
            <p>Vous cherchiez peut-être :
                <?php foreach ($similaires as $similaire): ?>
                    <a class="tag" href="?action=tag&amp;tag=<?= rawurlencode($similaire) ?>">#<?= TexteTouite::e($similaire) ?></a>
                <?php endforeach; ?>
            </p>
        <?php endif; ?>
    </section>
<?php else: ?>
    <?= $this->renderView('liste_touites', ['touites' => $touites]) ?>
<?php endif; ?>
