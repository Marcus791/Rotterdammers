<?php
require __DIR__ . '/includes/bootstrap.php';

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $username = post('gebruikersnaam');
    $user = find_user($username);

    if ($user && $user['rol'] === 'admin' && password_verify(post('wachtwoord', false), $user['wachtwoord_hash'])) {
        login_user($user);
        redirect('admin.php');
    }
    $error = 'Dit is geen geldig admin account.';
}

$title = 'Admin login';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="auth-page">
        <div class="auth-photo"><img src="<?= pexels(1267320, 1200) ?>" alt="Keuken"></div>
        <div class="auth-panel">
            <p class="kicker">Beheer</p>
            <h1>Admin login</h1>
            <p>Alleen accounts die als admin zijn aangemaakt krijgen toegang.</p>
            <form method="post">
                <?= csrf_field() ?>
                <label for="gebruikersnaam">Gebruikersnaam</label>
                <input id="gebruikersnaam" name="gebruikersnaam" value="<?= e($username) ?>" autocomplete="username" required>
                <label for="wachtwoord">Wachtwoord</label>
                <input id="wachtwoord" name="wachtwoord" type="password" autocomplete="current-password" required>
                <p class="form-message"><?= e($error) ?></p>
                <button type="submit">Inloggen als admin</button>
            </form>
            <div class="auth-links">
                <a href="login.php">Terug naar normale login</a>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
