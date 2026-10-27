<?php

session_start();
date_default_timezone_set('Europe/Amsterdam');

const DEFAULT_IMAGE = 'https://images.pexels.com/photos/1279330/pexels-photo-1279330.jpeg?auto=compress&cs=tinysrgb&w=900';

// ===== Database =====

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dir = __DIR__ . '/../data';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $pdo = new PDO('sqlite:' . $dir . '/kookboek.sqlite', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        require_once __DIR__ . '/database.php';
        setup_database($pdo);
    }
    return $pdo;
}

function query(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function find_user(string $username): ?array
{
    return query('SELECT * FROM gebruikers WHERE gebruikersnaam = ?', [$username])->fetch() ?: null;
}

function find_recipe(int $id): ?array
{
    return query(
        'SELECT r.*, t.naam AS thema FROM recepten r JOIN themas t ON t.id = r.thema_id WHERE r.id = ?',
        [$id]
    )->fetch() ?: null;
}

// Alleen bestellingen van de ingelogde gebruiker, zodat niemand andermans bestelling kan zien of betalen.
function find_own_order(int $id): ?array
{
    return query(
        'SELECT o.*, r.naam AS recept FROM orders o LEFT JOIN recepten r ON r.id = o.recept_id WHERE o.id = ? AND o.gebruiker_id = ?',
        [$id, current_user()['id'] ?? 0]
    )->fetch() ?: null;
}

function payment_status(string $status): string
{
    return [
        'open' => 'Nog niet betaald',
        'betaald' => 'Betaald',
        'mislukt' => 'Betaling mislukt',
        'geannuleerd' => 'Betaling geannuleerd',
    ][$status] ?? $status;
}

function recipe_steps(int $recipeId): array
{
    return query('SELECT titel, tekst, foto FROM stappen WHERE recept_id = ? ORDER BY volgorde', [$recipeId])->fetchAll();
}

function all_themes(): array
{
    return query('SELECT * FROM themas ORDER BY id')->fetchAll();
}

// ===== Hulpfuncties =====

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function post(string $key, bool $trim = true): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? ($trim ? trim($value) : $value) : '';
}

function post_list(string $key): array
{
    $values = $_POST[$key] ?? [];
    if (!is_array($values)) {
        return [];
    }
    return array_map(fn($value) => is_string($value) ? trim($value) : '', array_values($values));
}

function get(string $key): string
{
    $value = $_GET[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function pexels(int $id, int $width = 800): string
{
    return "https://images.pexels.com/photos/$id/pexels-photo-$id.jpeg?auto=compress&cs=tinysrgb&w=$width";
}

function format_price(float $price): string
{
    return '€ ' . number_format($price, 2, ',', '.');
}

function ingredient_list(string $text): array
{
    return preg_split('/\s*[,\n]\s*/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// ===== Inloggen en rechten =====

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return (current_user()['rol'] ?? '') === 'admin';
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => (int) $user['id'], 'gebruikersnaam' => $user['gebruikersnaam'], 'rol' => $user['rol']];
}

function require_login(string $reason = 'Log in om verder te gaan.'): void
{
    if (!current_user()) {
        // Onthoud deze pagina, zodat je na het inloggen hier terugkomt.
        $page = basename($_SERVER['SCRIPT_NAME']);
        $query = $_SERVER['QUERY_STRING'] ?? '';
        $_SESSION['na_login'] = ['url' => $query !== '' ? "$page?$query" : $page, 'reden' => $reason];
        redirect('login.php');
    }
}

function redirect_after_login(string $default): never
{
    $url = $_SESSION['na_login']['url'] ?? $default;
    unset($_SESSION['na_login']);
    redirect($url);
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect('admin-login.php');
    }
}

function csrf_field(): string
{
    $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}

function check_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', post('csrf'))) {
        http_response_code(400);
        exit('Ongeldig formulier. Ga terug en probeer het opnieuw.');
    }
}

// ===== Recepten opslaan en verwijderen =====

function recipe_input(): array
{
    return [
        'id' => (int) post('id'),
        'action' => post('action'),
        'naam' => post('naam'),
        'ingredienten' => post('ingredienten'),
        'thema_id' => (int) post('thema_id'),
        'tijd' => post('tijd'),
        'prijs' => str_replace(',', '.', post('prijs')),
        'foto' => post('foto'),
        'stappen' => step_input(),
    ];
}

function step_input(): array
{
    $titles = post_list('stap_titel');
    $texts = post_list('stap_tekst');
    $photos = post_list('stap_foto');

    $steps = [];
    for ($i = 0; $i < max(count($titles), count($texts), count($photos)); $i++) {
        $step = ['titel' => $titles[$i] ?? '', 'tekst' => $texts[$i] ?? '', 'foto' => $photos[$i] ?? ''];
        // Helemaal lege stappen slaan we over.
        if (implode('', $step) !== '') {
            $steps[] = $step;
        }
    }
    return $steps;
}

// Een lege foto mag ook, dan wordt er geen foto getoond.
function valid_photo_url(string $url): bool
{
    return $url === '' || (filter_var($url, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $url));
}

function validate_recipe(array $r): ?string
{
    if ($r['naam'] === '') return 'Vul een naam in.';
    if ($r['ingredienten'] === '') return 'Vul de ingrediënten in.';
    if (!query('SELECT 1 FROM themas WHERE id = ?', [$r['thema_id']])->fetch()) return 'Kies een geldige categorie.';
    if (!ctype_digit($r['tijd']) || (int) $r['tijd'] < 1) return 'Vul een geldige tijd in minuten in.';
    if (!is_numeric($r['prijs']) || (float) $r['prijs'] < 0) return 'Vul een geldige prijs in.';
    if (!valid_photo_url($r['foto'])) return 'De foto moet een geldige link zijn (https://...).';
    foreach ($r['stappen'] as $i => $step) {
        $number = $i + 1;
        if ($step['titel'] === '') return "Vul een titel in bij stap $number.";
        if ($step['tekst'] === '') return "Vul de uitleg in bij stap $number.";
        if (!valid_photo_url($step['foto'])) return "De foto bij stap $number moet een geldige link zijn (https://...).";
    }
    return null;
}

function handle_recipe_post(array $input, string $back): string
{
    // Alleen een admin mag recepten toevoegen, aanpassen of verwijderen.
    require_admin();
    check_csrf();
    $existing = $input['id'] ? find_recipe($input['id']) : null;

    if ($input['id'] && !$existing) {
        http_response_code(404);
        exit('Dit recept bestaat niet.');
    }

    if ($input['action'] === 'delete' && $existing) {
        query('DELETE FROM recepten WHERE id = ?', [$existing['id']]);
        redirect($back);
    }

    $error = validate_recipe($input);
    if ($error) {
        return $error;
    }

    $values = [
        $input['naam'],
        $input['ingredienten'],
        $input['thema_id'],
        (int) $input['tijd'],
        round((float) $input['prijs'], 2),
        $input['foto'] ?: DEFAULT_IMAGE,
    ];

    // Recept en stappen samen opslaan, zodat het nooit half lukt.
    db()->beginTransaction();
    if ($existing) {
        $id = (int) $existing['id'];
        query('UPDATE recepten SET naam = ?, ingredienten = ?, thema_id = ?, tijd = ?, prijs = ?, foto = ? WHERE id = ?', [...$values, $id]);
        query('DELETE FROM stappen WHERE recept_id = ?', [$id]);
    } else {
        query('INSERT INTO recepten (naam, ingredienten, thema_id, tijd, prijs, foto, gebruiker_id) VALUES (?, ?, ?, ?, ?, ?, ?)', [...$values, current_user()['id']]);
        $id = (int) db()->lastInsertId();
    }
    foreach ($input['stappen'] as $i => $step) {
        query(
            'INSERT INTO stappen (recept_id, volgorde, titel, tekst, foto) VALUES (?, ?, ?, ?, ?)',
            [$id, $i + 1, $step['titel'], $step['tekst'], $step['foto'] ?: null]
        );
    }
    db()->commit();
    redirect($back);
}
