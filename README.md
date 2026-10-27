# Kookboek – Rotterdammers

Een receptenwebsite in PHP. Bezoekers kunnen recepten zoeken en bekijken. Wie
een account heeft, kan de ingrediënten van een recept als maaltijdpakket
bestellen. Een admin beheert de recepten en de bestellingen.

## Wat kan wie?

| Rol | Kan |
| --- | --- |
| Bezoeker (niet ingelogd) | Recepten, thema's en het weekmenu bekijken. Bij **Bestellen** word je naar de inlogpagina gestuurd. |
| Gebruiker | Inloggen of registreren en een recept bestellen en betalen. Gebruikers kunnen **geen** recepten toevoegen. |
| Admin | Recepten toevoegen, aanpassen en verwijderen, en bestellingen bekijken en verwijderen (pagina **Admin**), met per bestelling het bedrag en de betaalstatus. Bij een recept kan de admin zelf kiezen hoeveel bereidingsstappen er zijn, elk met een titel, uitleg en optioneel een foto. |

Het bestelformulier controleert de invoer:

- **Telefoonnummer:** Nederlands nummer, bijvoorbeeld `06 12345678`, `010-1234567` of `+31 6 12345678`.
- **Adres:** aparte velden voor straat, huisnummer (alleen cijfers), toevoeging (optioneel), postcode (`3011 AA`) en plaats.

## Betalen (test-iDEAL)

De prijs van een recept is per persoon, dus het bedrag is prijs × aantal personen.
Na **Verder naar betalen** kom je op een test-iDEAL-pagina (`betalen.php`):

1. Kies je bank.
2. Op het testbank-scherm kies je zelf: **Betaling goedkeuren**, **Betaling laten mislukken** of **Annuleren**.
3. Je komt terug op de bestelpagina met de uitkomst. Is de betaling mislukt of geannuleerd, dan kun je opnieuw betalen.

Er wordt geen echt geld afgeschreven. Een bestelling heeft een van deze statussen: `open`
(nog niet betaald), `betaald`, `mislukt` of `geannuleerd`. Alleen degene die de bestelling
plaatste kan hem bekijken en betalen.

## Starten

Nodig: PHP 8.1 of hoger met de extensie `pdo_sqlite`.

```bash
php -S localhost:8000 router.php
```

Open daarna <http://localhost:8000>.

De database (`data/kookboek.sqlite`) wordt bij de eerste keer openen vanzelf
aangemaakt, met een admin-account en een paar voorbeeldrecepten. Verwijder dit
bestand als je opnieuw wilt beginnen.

## Admin-account

| Gebruikersnaam | Wachtwoord |
| --- | --- |
| `admin` | `admin123` |

Inloggen kan via **Inloggen** of via de admin-login (`admin-login.php`).
