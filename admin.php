<?php
require __DIR__ . '/includes/bootstrap.php';
require_admin();

$error = '';
$values = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (post('action') === 'delete-order') {
        check_csrf();
        $order = query('SELECT contact_id FROM orders WHERE id = ?', [(int) post('id')])->fetch();
        if ($order) {
            query('DELETE FROM orders WHERE id = ?', [(int) post('id')]);
            query('DELETE FROM contact WHERE id = ?', [$order['contact_id']]);
        }
        redirect('admin.php#bestellingen');
    }
    $values = recipe_input();
    $error = handle_recipe_post($values, 'admin.php');
}

$recipes = query('SELECT r.*, t.naam AS thema FROM recepten r JOIN themas t ON t.id = r.thema_id ORDER BY r.id DESC')->fetchAll();

$orders = query('
    SELECT o.id, o.personen, o.besteld_op, c.naam, c.telefoonnummer, c.adres,
           r.naam AS recept, r.foto, t.naam AS thema
    FROM orders o
    JOIN contact c ON c.id = o.contact_id
    LEFT JOIN recepten r ON r.id = o.recept_id
    LEFT JOIN themas t ON t.id = o.thema_id
    ORDER BY o.id DESC
')->fetchAll();

$title = 'Admin';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="page-head">
        <div class="wrap">
            <p class="kicker">Beheer</p>
            <h1>Adminpaneel</h1>
            <p>Voeg recepten toe en beheer alle recepten en bestellingen op de website.</p>
        </div>
    </section>

    <section class="section">
        <div class="wrap account-grid">
            <?php
            $formTitle = 'Recept toevoegen';
            $buttonText = 'Recept toevoegen';
            require __DIR__ . '/includes/recipe-form.php';
            ?>
            <section>
                <div class="section-title">
                    <div>
                        <p class="kicker">Alle recepten</p>
                        <h2>Beheren</h2>
                    </div>
                </div>
                <?php foreach ($recipes as $recipe): ?>
                    <?php $showEdit = true; require __DIR__ . '/includes/manage-card.php'; ?>
                <?php endforeach ?>
            </section>
        </div>
    </section>

    <section class="section" id="bestellingen">
        <div class="wrap">
            <div class="section-title">
                <div>
                    <p class="kicker">Orders</p>
                    <h2>Bestellingen</h2>
                </div>
            </div>
            <?php foreach ($orders as $order): ?>
                <article class="manage-card">
                    <img src="<?= e($order['foto'] ?? DEFAULT_IMAGE) ?>" alt="<?= e($order['recept'] ?? 'Recept') ?>">
                    <div>
                        <strong><?= e($order['recept'] ?? '(recept verwijderd)') ?> · <?= (int) $order['personen'] ?> personen</strong>
                        <span><?= e($order['naam']) ?> · <?= e($order['telefoonnummer']) ?> · <?= e($order['adres']) ?></span>
                        <span><?= e($order['thema'] ?? '-') ?> · besteld op <?= e(date('d-m-Y H:i', strtotime($order['besteld_op'] . ' UTC'))) ?></span>
                    </div>
                    <div class="manage-actions">
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete-order">
                            <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
                            <button class="delete-button" type="submit">Verwijderen</button>
                        </form>
                    </div>
                </article>
            <?php endforeach ?>
            <?php if (!$orders): ?>
                <p>Er zijn nog geen bestellingen.</p>
            <?php endif ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
