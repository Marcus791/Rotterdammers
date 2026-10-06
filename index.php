<?php
require __DIR__ . '/includes/bootstrap.php';

$featured = [
    [1279330, 'Avondeten · 30 min', 'Pasta met kip', 'Makkelijk, snel en geschikt voor een normale doordeweekse dag.', 'recept-detail.php?id=1'],
    [1640772, 'Vegetarisch · 20 min', 'Groente bowl', 'Een frisse maaltijd met rijst en verschillende groenten.', 'recept-detail.php?id=2'],
    [2097090, 'Lunch · 15 min', 'Snelle lunch', 'Een simpele lunch die je makkelijk kunt aanpassen.', 'recepten.php?thema=2'],
];

require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="home-visual">
        <img src="<?= pexels(1640777, 1600) ?>" alt="Tafel met eten">
        <div class="home-overlay">
            <p>Makkelijk koken</p>
            <h1>Wat eten we vandaag?</h1>
            <span>Zoek een recept op naam, ingrediënt of categorie.</span>
            <a class="main-button" href="recepten.php">Bekijk recepten</a>
        </div>
    </section>

    <section class="section">
        <div class="wrap">
            <div class="section-title">
                <div>
                    <p class="kicker">Uitgelicht</p>
                    <h2>Recepten voor deze week</h2>
                </div>
                <a href="recepten.php">Bekijk alles</a>
            </div>
            <div class="visual-grid">
                <?php foreach ($featured as [$photo, $label, $name, $text, $link]): ?>
                    <a class="food-card" href="<?= $link ?>">
                        <img src="<?= pexels($photo) ?>" alt="<?= e($name) ?>">
                        <div>
                            <small><?= e(mb_strtoupper($label)) ?></small>
                            <h3><?= e($name) ?></h3>
                            <p><?= e($text) ?></p>
                        </div>
                    </a>
                <?php endforeach ?>
            </div>
        </div>
    </section>

    <section class="category-strip">
        <div class="wrap">
            <p class="kicker">Kies een categorie</p>
            <div class="category-links">
                <?php foreach (all_themes() as $theme): ?>
                    <a href="recepten.php?thema=<?= $theme['id'] ?>"><?= e($theme['naam']) ?></a>
                <?php endforeach ?>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
