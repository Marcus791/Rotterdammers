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

function require_login(): void
{
    if (!current_user()) {
        redirect('login.php');
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect('admin-login.php');
    }
}

function can_manage(array $recipe): bool
{
    $user = current_user();
    return $user && ($user['rol'] === 'admin' || (int) $recipe['gebruiker_id'] === $user['id']);
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
    ];
}

function validate_recipe(array $r): ?string
{
    if ($r['naam'] === '') return 'Vul een naam in.';
    if ($r['ingredienten'] === '') return 'Vul de ingrediënten in.';
    if (!query('SELECT 1 FROM themas WHERE id = ?', [$r['thema_id']])->fetch()) return 'Kies een geldige categorie.';
    if (!ctype_digit($r['tijd']) || (int) $r['tijd'] < 1) return 'Vul een geldige tijd in minuten in.';
    if (!is_numeric($r['prijs']) || (float) $r['prijs'] < 0) return 'Vul een geldige prijs in.';
    if ($r['foto'] !== '' && (!filter_var($r['foto'], FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $r['foto']))) {
        return 'De foto moet een geldige link zijn (https://...).';
    }
    return null;
}

function handle_recipe_post(array $input, string $back): string
{
    check_csrf();
    $existing = $input['id'] ? find_recipe($input['id']) : null;

    if ($input['id'] && (!$existing || !can_manage($existing))) {
        http_response_code(403);
        exit('Je mag dit recept niet aanpassen.');
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

    if ($existing) {
        query('UPDATE recepten SET naam = ?, ingredienten = ?, thema_id = ?, tijd = ?, prijs = ?, foto = ? WHERE id = ?', [...$values, $existing['id']]);
    } else {
        query('INSERT INTO recepten (naam, ingredienten, thema_id, tijd, prijs, foto, gebruiker_id) VALUES (?, ?, ?, ?, ?, ?, ?)', [...$values, current_user()['id']]);
    }
    redirect($back);
}
