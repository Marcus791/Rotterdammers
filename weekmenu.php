<?php
require __DIR__ . '/includes/bootstrap.php';

$week = [
    ['Maandag', 'Pasta', 1279330, 'recept-detail.php?id=1'],
    ['Dinsdag', 'Groente bowl', 1640772, 'recept-detail.php?id=2'],
    ['Woensdag', 'Curry', 2474661, 'recept-detail.php?id=3'],
    ['Donderdag', 'Salade', 2097090, 'recepten.php?thema=2'],
    ['Vrijdag', 'Wereldgerecht', 958545, 'recepten.php?thema=6'],
];

$title = 'Weekmenu';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="page-head">
        <div class="wrap">
            <p class="kicker">Deze week</p>
            <h1>Weekmenu</h1>
            <p>Vijf gerechten voor maandag tot en met vrijdag.</p>
        </div>
    </section>

    <section class="section">
        <div class="wrap week-photo-grid">
            <?php foreach ($week as [$day, $dish, $photo, $link]): ?>
                <a class="week-photo-card" href="<?= $link ?>">
                    <img src="<?= pexels($photo, 700) ?>" alt="<?= e($dish) ?>">
                    <span><?= e($day) ?></span>
                    <h3><?= e($dish) ?></h3>
                </a>
            <?php endforeach ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
