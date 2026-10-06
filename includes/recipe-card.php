<article class="food-card">
    <img src="<?= e($recipe['foto']) ?>" alt="<?= e($recipe['naam']) ?>">
    <div>
        <small><?= e(mb_strtoupper($recipe['thema'])) ?> · <?= (int) $recipe['tijd'] ?> MIN · <?= format_price($recipe['prijs']) ?></small>
        <h3><?= e($recipe['naam']) ?></h3>
        <p><?= e($recipe['ingredienten']) ?></p>
        <a href="recept-detail.php?id=<?= (int) $recipe['id'] ?>">Bekijk recept</a>
    </div>
</article>
