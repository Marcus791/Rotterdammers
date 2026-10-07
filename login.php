<?php
require __DIR__ . '/includes/bootstrap.php';

$error = '';
$username = '';
$notice = $_SESSION['na_login']['reden'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $username = post('gebruikersnaam');
    $user = find_user($username);

    if ($user && password_verify(post('wachtwoord', false), $user['wachtwoord_hash'])) {
        login_user($user);
        redirect_after_login($user['rol'] === 'admin' ? 'admin.php' : 'recepten.php');
    }
    $error = 'Gebruikersnaam of wachtwoord klopt niet.';
}

$title = 'Inloggen';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="auth-page">
        <div class="auth-photo"><img src="<?= pexels(1640774, 1200) ?>" alt="Verse maaltijd"></div>
        <div class="auth-panel">
            <p class="kicker">Welkom terug</p>
            <h1>Inloggen</h1>
            <p>Log in om recepten te bestellen.</p>
            <?php if ($notice): ?>
                <p class="auth-notice"><?= e($notice) ?></p>
            <?php endif ?>
            <form method="post">
                <?= csrf_field() ?>
                <label for="gebruikersnaam">Gebruikersnaam</label>
                <input id="gebruikersnaam" name="gebruikersnaam" value="<?= e($username) ?>" autocomplete="username" required>
                <label for="wachtwoord">Wachtwoord</label>
                <input id="wachtwoord" name="wachtwoord" type="password" autocomplete="current-password" required>
                <p class="form-message"><?= e($error) ?></p>
                <button type="submit">Inloggen</button>
            </form>
            <div class="auth-links">
                <a href="registreren.php">Nog geen account? Registreren</a>
                <a href="admin-login.php">Speciale admin login</a>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
