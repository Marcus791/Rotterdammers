<?php
require __DIR__ . '/includes/bootstrap.php';

$recipe = find_recipe((int) (get('id') ?: 1));

if (!$recipe) {
    http_response_code(404);
    $title = 'Recept niet gevonden';
    require __DIR__ . '/includes/header.php';
    ?>
    <main>
        <section class="page-head">
            <div class="wrap">
                <p class="kicker">Oeps</p>
                <h1>Recept niet gevonden</h1>
                <p><a class="text-link" href="recepten.php">Terug naar recepten</a></p>
            </div>
        </section>
    </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$steps = query('SELECT * FROM stappen WHERE recept_id = ? ORDER BY volgorde', [$recipe['id']])->fetchAll();

$title = $recipe['naam'];
require __DIR__ . '/includes/header.php';
?>
<main>
    <article class="recipe-article">
        <div class="wrap article-intro">
            <a class="back" href="recepten.php">Terug naar recepten</a>
            <p class="kicker"><?= e($recipe['thema']) ?></p>
            <h1><?= e($recipe['naam']) ?></h1>
            <?php if ($recipe['inleiding']): ?>
                <p class="article-lead"><?= e($recipe['inleiding']) ?></p>
            <?php endif ?>
            <div class="article-meta">
                <span><b><?= (int) $recipe['tijd'] ?></b> minuten</span>
                <span><b><?= format_price($recipe['prijs']) ?></b> totaal</span>
                <?php if ($recipe['personen']): ?>
                    <span><b><?= (int) $recipe['personen'] ?></b> personen</span>
                <?php endif ?>
                <a class="button" href="bestellen.php?recept=<?= (int) $recipe['id'] ?>">Bestel dit recept</a>
            </div>
        </div>
        <img class="article-cover" src="<?= e($recipe['foto']) ?>" alt="<?= e($recipe['naam']) ?>">

        <div class="article-body">
            <section class="ingredients-box">
                <h2>Dit heb je nodig</h2>
                <ul>
                    <?php foreach (ingredient_list($recipe['ingredienten']) as $ingredient): ?>
                        <li><?= e($ingredient) ?></li>
                    <?php endforeach ?>
                </ul>
            </section>

            <?php foreach ($steps as $i => $step): ?>
                <section class="story-step<?= $i % 2 ? ' reverse' : '' ?>">
                    <div>
                        <span>STAP <?= (int) $step['volgorde'] ?></span>
                        <h2><?= e($step['titel']) ?></h2>
                        <p><?= e($step['tekst']) ?></p>
                    </div>
                    <?php if ($step['foto']): ?>
                        <img src="<?= e($step['foto']) ?>" alt="<?= e($step['titel']) ?>">
                    <?php endif ?>
                </section>
            <?php endforeach ?>

            <p class="source-note">Foodfoto's via Pexels.</p>
        </div>
    </article>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
