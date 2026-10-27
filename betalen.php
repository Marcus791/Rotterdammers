<?php
require __DIR__ . '/includes/bootstrap.php';
require_login('Log in om je bestelling te betalen.');

// Testversie van een iDEAL-betaling. Er gaat geen echt geld over de lijn:
// na het kiezen van een bank bepaal je zelf of de betaling lukt, mislukt of wordt geannuleerd.
// Een echte betaalprovider (bijvoorbeeld Mollie) zou dezelfde statussen terugsturen.
const BANKS = [
    'abnamro' => ['ABN AMRO', '#00857a'],
    'asn' => ['ASN Bank', '#d6392f'],
    'bunq' => ['bunq', '#2d8a4e'],
    'ing' => ['ING', '#ff6200'],
    'knab' => ['Knab', '#183e5c'],
    'n26' => ['N26', '#36a18b'],
    'nn' => ['Nationale-Nederlanden', '#ee7f00'],
    'rabobank' => ['Rabobank', '#000099'],
    'regiobank' => ['RegioBank', '#c8102e'],
    'revolut' => ['Revolut', '#191c1f'],
    'sns' => ['SNS', '#5c2d91'],
    'triodos' => ['Triodos Bank', '#00694b'],
    'vanlanschot' => ['Van Lanschot', '#0b5c55'],
    'yoursafe' => ['Yoursafe', '#1797d4'],
];

$order = find_own_order((int) get('order'));

if (!$order) {
    http_response_code(404);
    exit('Deze bestelling bestaat niet.');
}

$back = 'bestellen.php?order=' . $order['id'];
$self = 'betalen.php?order=' . $order['id'];

if ($order['status'] === 'betaald') {
    redirect($back);
}

// Een bank is gekozen en de betaling wacht op goedkeuring.
$atBank = $order['status'] === 'open' && $order['bank'] !== null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = post('action');
    // "status != 'betaald'" voorkomt dat een betaalde bestelling vanuit een tweede tabblad weer wordt veranderd.
    $update = fn(string $set, array $params = []) =>
        query("UPDATE orders SET $set WHERE id = ? AND status != 'betaald'", [...$params, $order['id']]);

    if ($action === 'bank' && isset(BANKS[post('bank')])) {
        $update("bank = ?, status = 'open'", [BANKS[post('bank')][0]]);
        redirect($self);
    }
    if ($action === 'other-bank') {
        $update("bank = NULL, status = 'open'");
        redirect($self);
    }
    if ($action === 'cancel') {
        $update("status = 'geannuleerd'");
        redirect($back);
    }
    if ($atBank && $action === 'approve') {
        $update("status = 'betaald', betaald_op = CURRENT_TIMESTAMP");
        redirect($back);
    }
    if ($atBank && $action === 'fail') {
        $update("status = 'mislukt'");
        redirect($back);
    }
    redirect($self);
}

$summary = sprintf('Bestelling #%d · %s · %d personen', $order['id'], $order['recept'] ?? 'Recept', $order['personen']);
?>
<!doctype html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Betalen | Kookboek</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="pay-page">
    <header class="pay-header">
        <div class="wrap pay-top">
            <span class="pay-badge">iDEAL · TEST</span>
            <div class="pay-shop">
                <span>Kookboek</span>
                <strong><?= format_price($order['bedrag']) ?></strong>
            </div>
        </div>
    </header>
    <p class="pay-test-note">Dit is een testbetaling. Er wordt geen echt geld afgeschreven.</p>

    <main class="wrap pay-main">
        <?php if (!$atBank): ?>
            <form method="post">
                <?= csrf_field() ?>
                <button class="pay-back" name="action" value="cancel">Terug</button>
            </form>

            <h1>Kies je bank</h1>
            <p class="pay-summary"><?= e($summary) ?></p>

            <form method="post" class="bank-grid">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="bank">
                <?php foreach (BANKS as $key => [$name, $color]): ?>
                    <button class="bank-option" name="bank" value="<?= $key ?>">
                        <span class="bank-icon" style="--bank: <?= $color ?>"><?= e(mb_substr($name, 0, 1)) ?></span>
                        <?= e($name) ?>
                    </button>
                <?php endforeach ?>
            </form>
        <?php else: ?>
            <section class="bank-screen">
                <p class="kicker">Testbank</p>
                <h1><?= e($order['bank']) ?></h1>
                <p>Je betaalt <strong><?= format_price($order['bedrag']) ?></strong> aan Kookboek.</p>
                <p class="pay-summary"><?= e($summary) ?></p>
                <p class="pay-explain">Bij een echte betaling log je hier in bij je bank en keur je de betaling goed.
                    In deze test kies je zelf wat er gebeurt.</p>

                <form method="post" class="pay-actions">
                    <?= csrf_field() ?>
                    <button name="action" value="approve">Betaling goedkeuren</button>
                    <button class="secondary-button" name="action" value="fail">Betaling laten mislukken</button>
                    <button class="secondary-button" name="action" value="cancel">Annuleren</button>
                    <button class="pay-back" name="action" value="other-bank">Andere bank kiezen</button>
                </form>
            </section>
        <?php endif ?>
    </main>
</body>

</html>
