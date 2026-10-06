<?php
require __DIR__ . '/includes/bootstrap.php';

$title = "Thema's";
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="page-head">
        <div class="wrap">
            <p class="kicker">Inspiratie</p>
            <h1>Kies een thema</h1>
            <p>Van snel ontbijt tot vegetarisch avondeten.</p>
        </div>
    </section>

    <section class="section">
        <div class="wrap visual-grid">
            <?php foreach (all_themes() as $theme): ?>
                <a class="food-card" href="recepten.php?thema=<?= $theme['id'] ?>">
                    <img src="<?= e($theme['foto']) ?>" alt="<?= e($theme['naam']) ?>">
                    <div>
                        <small><?= e(mb_strtoupper($theme['label'])) ?></small>
                        <h3><?= e($theme['naam']) ?></h3>
                        <p><?= e($theme['beschrijving']) ?></p>
                    </div>
                </a>
            <?php endforeach ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
