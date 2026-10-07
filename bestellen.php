<?php
require __DIR__ . '/includes/bootstrap.php';
require_login('Je moet ingelogd zijn om een recept te bestellen.');

// Deze patronen gebruikt zowel de browser (pattern="...") als PHP.
// Telefoon: Nederlands nummer, bv. 06 12345678, 010-1234567 of +31 6 12345678.
const PHONE_PATTERN = '(\+31|0031|0)[ \-]?[1-9]([ \-]?[0-9]){8}';
const POSTCODE_PATTERN = '[1-9][0-9]{3} ?[A-Za-z]{2}';

$error = '';
$values = [
    'recept_id' => (int) get('recept'), 'personen' => 4, 'naam' => '', 'telefoonnummer' => '',
    'straat' => '', 'huisnummer' => '', 'toevoeging' => '', 'postcode' => '', 'plaats' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $values = [
        'recept_id' => (int) post('recept_id'),
        'personen' => post('personen'),
        'naam' => post('naam'),
        'telefoonnummer' => post('telefoonnummer'),
        'straat' => post('straat'),
        'huisnummer' => post('huisnummer'),
        'toevoeging' => post('toevoeging'),
        'postcode' => strtoupper(post('postcode')),
        'plaats' => post('plaats'),
    ];
    $recipe = find_recipe($values['recept_id']);

    if (!$recipe) {
        $error = 'Kies een recept.';
    } elseif (!ctype_digit($values['personen']) || $values['personen'] < 1 || $values['personen'] > 20) {
        $error = 'Kies tussen 1 en 20 personen.';
    } elseif (!preg_match('/^(?=.*\p{L})[\p{L}\p{M} .\'-]{2,60}$/u', $values['naam'])) {
        $error = 'Vul een geldige naam in (alleen letters).';
    } elseif (!preg_match('/^' . PHONE_PATTERN . '$/', $values['telefoonnummer'])) {
        $error = 'Vul een geldig Nederlands telefoonnummer in, bijvoorbeeld 06 12345678.';
    } elseif (!preg_match('/^(?=.*\p{L}{2})[\p{L}\p{M}0-9 .\'-]{2,60}$/u', $values['straat'])) {
        $error = 'Vul een geldige straatnaam in.';
    } elseif (!preg_match('/^[1-9][0-9]{0,4}$/', $values['huisnummer'])) {
        $error = 'Vul een geldig huisnummer in (alleen cijfers).';
    } elseif (!preg_match('/^[A-Za-z0-9]{0,4}$/', $values['toevoeging'])) {
        $error = 'De toevoeging mag maximaal 4 letters of cijfers zijn.';
    } elseif (!preg_match('/^' . POSTCODE_PATTERN . '$/', $values['postcode']) || preg_match('/S[ADS]$/', $values['postcode'])) {
        $error = 'Vul een geldige postcode in, bijvoorbeeld 3011 AA.';
    } elseif (!preg_match('/^(?=.*\p{L}{2})[\p{L}\p{M} .\'-]{2,40}$/u', $values['plaats'])) {
        $error = 'Vul een geldige plaatsnaam in.';
    } else {
        // Opslaan in een vaste vorm: 0612345678 en "Straat 12-A, 3011 AA Rotterdam".
        $phone = preg_replace('/^(\+31|0031)/', '0', str_replace([' ', '-'], '', $values['telefoonnummer']));
        $postcode = str_replace(' ', '', $values['postcode']);
        $address = sprintf(
            '%s %s%s, %s %s %s',
            $values['straat'],
            $values['huisnummer'],
            $values['toevoeging'] !== '' ? '-' . strtoupper($values['toevoeging']) : '',
            substr($postcode, 0, 4),
            substr($postcode, 4),
            $values['plaats']
        );

        db()->beginTransaction();
        query('INSERT INTO contact (naam, telefoonnummer, adres) VALUES (?, ?, ?)', [$values['naam'], $phone, $address]);
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
                    <input id="naam" name="naam" value="<?= e($values['naam']) ?>" autocomplete="name" maxlength="60" required>

                    <label for="telefoonnummer">Telefoonnummer</label>
                    <input id="telefoonnummer" name="telefoonnummer" type="tel" value="<?= e($values['telefoonnummer']) ?>" autocomplete="tel" placeholder="06 12345678"
                        pattern="<?= e(PHONE_PATTERN) ?>" maxlength="20" title="Nederlands telefoonnummer, bijvoorbeeld 06 12345678 of 010 1234567" required>

                    <label for="straat">Straat</label>
                    <input id="straat" name="straat" value="<?= e($values['straat']) ?>" placeholder="Coolsingel" maxlength="60" required>

                    <div class="form-two">
                        <div>
                            <label for="huisnummer">Huisnummer</label>
                            <input id="huisnummer" name="huisnummer" value="<?= e($values['huisnummer']) ?>" inputmode="numeric" placeholder="40"
                                pattern="[1-9][0-9]{0,4}" maxlength="5" title="Alleen cijfers" required>
                        </div>
                        <div>
                            <label for="toevoeging">Toevoeging (optioneel)</label>
                            <input id="toevoeging" name="toevoeging" value="<?= e($values['toevoeging']) ?>" placeholder="A"
                                pattern="[A-Za-z0-9]{1,4}" maxlength="4" title="Maximaal 4 letters of cijfers">
                        </div>
                    </div>

                    <div class="form-two">
                        <div>
                            <label for="postcode">Postcode</label>
                            <input id="postcode" name="postcode" value="<?= e($values['postcode']) ?>" autocomplete="postal-code" placeholder="3011 AA"
                                pattern="<?= e(POSTCODE_PATTERN) ?>" maxlength="7" title="Postcode, bijvoorbeeld 3011 AA" required>
                        </div>
                        <div>
                            <label for="plaats">Plaats</label>
                            <input id="plaats" name="plaats" value="<?= e($values['plaats']) ?>" autocomplete="address-level2" placeholder="Rotterdam" maxlength="40" required>
                        </div>
                    </div>

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
