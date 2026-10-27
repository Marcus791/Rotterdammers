<?php

// Verhoog dit getal als de database verandert, en voeg een stap toe in setup_database().
const DB_VERSION = 2;

function setup_database(PDO $pdo): void
{
    if ((int) $pdo->query('PRAGMA user_version')->fetchColumn() >= DB_VERSION) {
        return;
    }

    $pdo->exec('BEGIN IMMEDIATE');
    $version = (int) $pdo->query('PRAGMA user_version')->fetchColumn();
    if ($version < 1) {
        create_database($pdo);
    } elseif ($version < 2) {
        add_payments($pdo);
    }
    $pdo->exec('PRAGMA user_version = ' . DB_VERSION);
    $pdo->exec('COMMIT');
}

// Versie 2: bestellingen hebben een bedrag, een betaalstatus en horen bij een gebruiker.
function add_payments(PDO $pdo): void
{
    $pdo->exec("
        ALTER TABLE orders ADD COLUMN gebruiker_id INTEGER REFERENCES gebruikers(id) ON DELETE SET NULL;
        ALTER TABLE orders ADD COLUMN bedrag REAL;
        ALTER TABLE orders ADD COLUMN status TEXT NOT NULL DEFAULT 'open' CHECK (status IN ('open', 'betaald', 'mislukt', 'geannuleerd'));
        ALTER TABLE orders ADD COLUMN bank TEXT;
        ALTER TABLE orders ADD COLUMN betaald_op TEXT;
        UPDATE orders SET bedrag = ROUND((SELECT prijs FROM recepten WHERE id = orders.recept_id) * personen, 2);
    ");
}

function create_database(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE gebruikers (
            id              INTEGER PRIMARY KEY,
            gebruikersnaam  TEXT NOT NULL UNIQUE COLLATE NOCASE,
            wachtwoord_hash TEXT NOT NULL,
            rol             TEXT NOT NULL DEFAULT 'gebruiker' CHECK (rol IN ('gebruiker', 'admin')),
            aangemaakt_op   TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE themas (
            id           INTEGER PRIMARY KEY,
            naam         TEXT NOT NULL UNIQUE,
            label        TEXT NOT NULL,
            beschrijving TEXT NOT NULL,
            foto         TEXT NOT NULL,
            seizoen      TEXT,
            feest        TEXT,
            dieet        TEXT
        );

        CREATE TABLE recepten (
            id            INTEGER PRIMARY KEY,
            naam          TEXT NOT NULL,
            ingredienten  TEXT NOT NULL,
            prijs         REAL NOT NULL CHECK (prijs >= 0),
            tijd          INTEGER NOT NULL CHECK (tijd > 0),
            foto          TEXT NOT NULL,
            inleiding     TEXT,
            personen      INTEGER,
            thema_id      INTEGER NOT NULL REFERENCES themas(id),
            gebruiker_id  INTEGER REFERENCES gebruikers(id) ON DELETE SET NULL,
            aangemaakt_op TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE stappen (
            id        INTEGER PRIMARY KEY,
            recept_id INTEGER NOT NULL REFERENCES recepten(id) ON DELETE CASCADE,
            volgorde  INTEGER NOT NULL,
            titel     TEXT NOT NULL,
            tekst     TEXT NOT NULL,
            foto      TEXT
        );

        CREATE TABLE contact (
            id             INTEGER PRIMARY KEY,
            naam           TEXT NOT NULL,
            telefoonnummer TEXT NOT NULL,
            adres          TEXT NOT NULL
        );

        CREATE TABLE orders (
            id         INTEGER PRIMARY KEY,
            thema_id   INTEGER REFERENCES themas(id),
            recept_id  INTEGER REFERENCES recepten(id) ON DELETE SET NULL,
            contact_id INTEGER NOT NULL REFERENCES contact(id),
            personen   INTEGER NOT NULL CHECK (personen > 0),
            besteld_op TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            gebruiker_id INTEGER REFERENCES gebruikers(id) ON DELETE SET NULL,
            bedrag     REAL,
            status     TEXT NOT NULL DEFAULT 'open' CHECK (status IN ('open', 'betaald', 'mislukt', 'geannuleerd')),
            bank       TEXT,
            betaald_op TEXT
        );
    ");

    $pdo->prepare("INSERT INTO gebruikers (gebruikersnaam, wachtwoord_hash, rol) VALUES ('admin', ?, 'admin')")
        ->execute([password_hash('admin123', PASSWORD_DEFAULT)]);

    $themes = [
        ['Ontbijt', 'Ochtend', 'Makkelijke recepten om je dag te beginnen.', 376464, 'Hele jaar', null, null],
        ['Lunch', 'Middag', 'Snelle en frisse lunchgerechten.', 2097090, 'Hele jaar', null, null],
        ['Avondeten', 'Avond', 'Warme gerechten voor na school of werk.', 1279330, 'Hele jaar', null, null],
        ['Pittig', 'Kruidig', 'Recepten met wat extra pit.', 2474661, 'Hele jaar', null, null],
        ['Vegetarisch', 'Zonder vlees', 'Groenten in de hoofdrol.', 1640772, 'Hele jaar', null, 'Vegetarisch'],
        ['Wereldkeuken', 'Werelds', 'Gerechten uit verschillende keukens.', 958545, 'Hele jaar', null, null],
    ];
    $stmt = $pdo->prepare('INSERT INTO themas (naam, label, beschrijving, foto, seizoen, feest, dieet) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($themes as [$naam, $label, $beschrijving, $foto, $seizoen, $feest, $dieet]) {
        $stmt->execute([$naam, $label, $beschrijving, pexels($foto), $seizoen, $feest, $dieet]);
    }

    $recipes = [
        ['Pasta met kip en groenten', '250 gram pasta, 200 gram kipfilet, 1 paprika, 1 ui, 200 ml kookroom, peper en zout', 3, 30, 9.50, 1279330,
            'Een makkelijk gerecht voor als je niet te lang in de keuken wilt staan.', 4],
        ['Vegetarische bowl', 'rijst, avocado, groenten', 5, 20, 7.25, 1640772, null, null],
        ['Pittige curry', 'rijst, curry, groenten, kruiden', 4, 25, 8.00, 2474661, null, null],
    ];
    $stmt = $pdo->prepare('INSERT INTO recepten (naam, ingredienten, thema_id, tijd, prijs, foto, inleiding, personen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($recipes as [$naam, $ingredienten, $thema, $tijd, $prijs, $foto, $inleiding, $personen]) {
        $stmt->execute([$naam, $ingredienten, $thema, $tijd, $prijs, pexels($foto, 1600), $inleiding, $personen]);
    }

    $steps = [
        [1, 'Alles voorbereiden', 'Snijd eerst de kip, paprika en ui. Zet ondertussen een pan met water op voor de pasta.', 4199098],
        [2, 'Koken en bakken', 'Kook de pasta volgens de verpakking. Bak de kip in een pan en voeg daarna de ui en paprika toe.', 4252137],
        [3, 'Alles bij elkaar', 'Voeg de kookroom toe. Meng daarna de pasta door de saus en breng het gerecht op smaak.', 1279330],
    ];
    $stmt = $pdo->prepare('INSERT INTO stappen (recept_id, volgorde, titel, tekst, foto) VALUES (1, ?, ?, ?, ?)');
    foreach ($steps as [$volgorde, $titel, $tekst, $foto]) {
        $stmt->execute([$volgorde, $titel, $tekst, pexels($foto, 1000)]);
    }
}
