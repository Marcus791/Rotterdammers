<article class="manage-card">
    <img src="<?= e($recipe['foto']) ?>" alt="<?= e($recipe['naam']) ?>">
    <div>
        <strong><?= e($recipe['naam']) ?></strong>
        <span><?= e($recipe['thema']) ?> · <?= (int) $recipe['tijd'] ?> min · <?= format_price($recipe['prijs']) ?></span>
    </div>
    <div class="manage-actions">
        <a class="button" href="admin.php?edit=<?= (int) $recipe['id'] ?>">Aanpassen</a>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int) $recipe['id'] ?>">
            <button class="delete-button" type="submit">Verwijderen</button>
        </form>
    </div>
</article>
