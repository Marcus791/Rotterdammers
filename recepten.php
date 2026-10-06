<?php
require __DIR__ . '/includes/bootstrap.php';

$search = get('q');
$themeId = (int) get('thema');
$sort = get('sort');

$sql = 'SELECT r.*, t.naam AS thema FROM recepten r JOIN themas t ON t.id = r.thema_id WHERE 1';
$params = [];
if ($search !== '') {
    $sql .= " AND (r.naam || ' ' || r.ingredienten) LIKE ? ESCAPE '\\'";
    $params[] = '%' . addcslashes($search, '%_\\') . '%';
}
if ($themeId) {
    $sql .= ' AND r.thema_id = ?';
    $params[] = $themeId;
}
$sql .= match ($sort) {
    'price' => ' ORDER BY r.prijs',
    'time' => ' ORDER BY r.tijd',
    default => ' ORDER BY r.id',
};
$recipes = query($sql, $params)->fetchAll();

$title = 'Recepten';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="page-head">
        <div class="wrap">
            <p class="kicker">Receptenoverzicht</p>
            <h1>Vind iets om te koken</h1>
            <p>Zoek op recept of ingrediënt en filter daarna op categorie, prijs of tijd.</p>
        </div>
    </section>

    <section class="section">
        <div class="wrap">
            <form class="filter-panel" method="get">
                <div>
                    <label for="q">Zoeken</label>
                    <input id="q" name="q" value="<?= e($search) ?>" placeholder="Bijvoorbeeld pasta, kip of tomaat">
                </div>
                <div>
                    <label for="thema">Categorie</label>
                    <select id="thema" name="thema" onchange="this.form.submit()">
                        <option value="">Alles</option>
                        <?php foreach (all_themes() as $theme): ?>
                            <option value="<?= $theme['id'] ?>" <?= $themeId === (int) $theme['id'] ? 'selected' : '' ?>><?= e($theme['naam']) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div>
                    <label for="sort">Sorteren</label>
                    <select id="sort" name="sort" onchange="this.form.submit()">
                        <option value="">Standaard</option>
                        <option value="price" <?= $sort === 'price' ? 'selected' : '' ?>>Prijs laag - hoog</option>
                        <option value="time" <?= $sort === 'time' ? 'selected' : '' ?>>Tijd kort - lang</option>
                    </select>
                </div>
                <button type="submit">Zoeken</button>
            </form>

            <div class="visual-grid">
                <?php foreach ($recipes as $recipe): ?>
                    <?php require __DIR__ . '/includes/recipe-card.php'; ?>
                <?php endforeach ?>
            </div>
            <?php if (!$recipes): ?>
                <p>Geen recepten gevonden.</p>
            <?php endif ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
