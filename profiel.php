<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$error = '';
$values = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = recipe_input();
    $error = handle_recipe_post($values, 'profiel.php');
} elseif (get('edit') !== '') {
    $recipe = find_recipe((int) get('edit'));
    $values = $recipe && can_manage($recipe) ? $recipe : [];
}

$sql = 'SELECT r.*, t.naam AS thema FROM recepten r JOIN themas t ON t.id = r.thema_id';
$recipes = is_admin()
    ? query("$sql ORDER BY r.id DESC")->fetchAll()
    : query("$sql WHERE r.gebruiker_id = ? ORDER BY r.id DESC", [current_user()['id']])->fetchAll();

$title = 'Mijn recepten';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="page-head">
        <div class="wrap">
            <p class="kicker">Mijn account</p>
            <h1>Mijn recepten</h1>
            <p>Plaats nieuwe recepten en pas je eigen recepten aan.</p>
        </div>
    </section>

    <section class="section">
        <div class="wrap account-grid">
            <?php
            $formTitle = 'Recept plaatsen';
            $buttonText = 'Recept opslaan';
            require __DIR__ . '/includes/recipe-form.php';
            ?>
            <section>
                <div class="section-title">
                    <div>
                        <p class="kicker">Beheren</p>
                        <h2>Geplaatste recepten</h2>
                    </div>
                </div>
                <?php foreach ($recipes as $recipe): ?>
                    <?php $showEdit = true; require __DIR__ . '/includes/manage-card.php'; ?>
                <?php endforeach ?>
                <?php if (!$recipes): ?>
                    <p>Je hebt nog geen recepten geplaatst.</p>
                <?php endif ?>
            </section>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
