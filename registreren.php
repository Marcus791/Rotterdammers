<?php
require __DIR__ . '/includes/bootstrap.php';

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $username = post('gebruikersnaam');
    $password = post('wachtwoord', false);

    if ($username === '' || mb_strlen($username) > 50) {
        $error = 'Vul een gebruikersnaam in (maximaal 50 tekens).';
    } elseif (mb_strlen($password) < 4) {
        $error = 'Het wachtwoord moet minstens 4 tekens hebben.';
    } elseif ($password !== post('wachtwoord2', false)) {
        $error = 'De wachtwoorden zijn niet hetzelfde.';
    } elseif (find_user($username)) {
        $error = 'Deze gebruikersnaam bestaat al.';
    } else {
        query('INSERT INTO gebruikers (gebruikersnaam, wachtwoord_hash) VALUES (?, ?)', [$username, password_hash($password, PASSWORD_DEFAULT)]);
        login_user(find_user($username));
        redirect('profiel.php');
    }
}

$title = 'Registreren';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="auth-page">
        <div class="auth-photo"><img src="<?= pexels(3184183, 1200) ?>" alt="Samen eten"></div>
        <div class="auth-panel">
            <p class="kicker">Nieuw account</p>
            <h1>Registreren</h1>
            <p>Maak een account om zelf recepten te plaatsen.</p>
            <form method="post">
                <?= csrf_field() ?>
                <label for="gebruikersnaam">Gebruikersnaam</label>
                <input id="gebruikersnaam" name="gebruikersnaam" value="<?= e($username) ?>" maxlength="50" autocomplete="username" required>
                <label for="wachtwoord">Wachtwoord</label>
                <input id="wachtwoord" name="wachtwoord" type="password" minlength="4" autocomplete="new-password" required>
                <label for="wachtwoord2">Wachtwoord herhalen</label>
                <input id="wachtwoord2" name="wachtwoord2" type="password" autocomplete="new-password" required>
                <p class="form-message"><?= e($error) ?></p>
                <button type="submit">Account maken</button>
            </form>
            <div class="auth-links">
                <a href="login.php">Al een account? Inloggen</a>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
