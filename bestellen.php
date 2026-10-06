<?php
require __DIR__ . '/includes/bootstrap.php';

$error = '';
$values = ['recept_id' => (int) get('recept'), 'personen' => 4, 'naam' => '', 'telefoonnummer' => '', 'adres' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $values = [
        'recept_id' => (int) post('recept_id'),
        'personen' => post('personen'),
        'naam' => post('naam'),
        'telefoonnummer' => post('telefoonnummer'),
        'adres' => post('adres'),
    ];
    $recipe = find_recipe($values['recept_id']);

    if (!$recipe) {
        $error = 'Kies een recept.';
    } elseif (!ctype_digit($values['personen']) || $values['personen'] < 1 || $values['personen'] > 20) {
        $error = 'Kies tussen 1 en 20 personen.';
    } elseif ($values['naam'] === '' || $values['adres'] === '') {
        $error = 'Vul je naam en adres in.';
    } elseif (!preg_match('/^\+?[0-9 ()-]{8,20}$/', $values['telefoonnummer'])) {
        $error = 'Vul een geldig telefoonnummer in.';
    } else {
        db()->beginTransaction();
        query('INSERT INTO contact (naam, telefoonnummer, adres) VALUES (?, ?, ?)', [$values['naam'], $values['telefoonnummer'], $values['adres']]);
        query('INSERT INTO orders (thema_id, recept_id, contact_id, personen) VALUES (?, ?, ?, ?)', [
            $recipe['thema_id'], $recipe['id'], db()->lastInsertId(), (int) $values['personen'],
        ]);
        db()->commit();
        redirect('bestellen.php?besteld=' . $recipe['id']);
    }
}

$ordered = get('besteld') !== '' ? find_recipe((int) get('besteld')) : null;
$selected = $ordered ?? find_recipe((int) $values['recept_id']);
$recipes = query('SELECT id, naam FROM recepten ORDER BY naam')->fetchAll();

$title = 'Bestellen';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="page-head">
        <div class="wrap">
            <p class="kicker">Maaltijdpakket</p>
            <h1>Bestellen</h1>
            <p>Bestel de ingrediënten van een recept en wij bezorgen ze bij je thuis.</p>
        </div>
    </section>

    <section class="section">
        <div class="wrap account-grid">
            <?php if ($ordered): ?>
                <div class="editor">
                    <h2>Bedankt voor je bestelling!</h2>
                    <p>We hebben je bestelling voor <strong><?= e($ordered['naam']) ?></strong> ontvangen. We nemen telefonisch contact met je op.</p>
                    <a class="button" href="recepten.php">Terug naar recepten</a>
                </div>
            <?php else: ?>
                <form method="post" class="editor">
                    <?= csrf_field() ?>
                    <h2>Bestelling plaatsen</h2>

                    <div class="form-two">
                        <div>
                            <label for="recept_id">Recept</label>
                            <select id="recept_id" name="recept_id" required>
                                <option value="">Kies een recept</option>
                                <?php foreach ($recipes as $recipe): ?>
                                    <option value="<?= $recipe['id'] ?>" <?= (int) $values['recept_id'] === (int) $recipe['id'] ? 'selected' : '' ?>><?= e($recipe['naam']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div>
                            <label for="personen">Aantal personen</label>
                            <input id="personen" name="personen" type="number" min="1" max="20" value="<?= e($values['personen']) ?>" required>
                        </div>
                    </div>

                    <label for="naam">Naam</label>
                    <input id="naam" name="naam" value="<?= e($values['naam']) ?>" autocomplete="name" required>

                    <label for="telefoonnummer">Telefoonnummer</label>
                    <input id="telefoonnummer" name="telefoonnummer" type="tel" value="<?= e($values['telefoonnummer']) ?>" autocomplete="tel" placeholder="06 12345678" required>

                    <label for="adres">Adres</label>
                    <input id="adres" name="adres" value="<?= e($values['adres']) ?>" autocomplete="street-address" placeholder="Straat 1, 3011 AA Rotterdam" required>

                    <p class="form-message"><?= e($error) ?></p>
                    <button type="submit">Bestelling plaatsen</button>
                </form>
            <?php endif ?>

            <section>
                <div class="section-title">
                    <div>
                        <p class="kicker">Jouw keuze</p>
                        <h2><?= $selected ? 'Dit recept bestel je' : 'Kies een recept' ?></h2>
                    </div>
                </div>
                <?php if ($selected): ?>
                    <?php $recipe = $selected; require __DIR__ . '/includes/recipe-card.php'; ?>
                <?php else: ?>
                    <p>Kies links een recept, of ga naar een recept en klik op "Bestel dit recept".</p>
                <?php endif ?>
            </section>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
