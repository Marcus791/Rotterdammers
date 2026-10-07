# Kookboek – Rotterdammers

Een receptenwebsite in PHP. Bezoekers kunnen recepten zoeken en bekijken. Wie
een account heeft, kan de ingrediënten van een recept als maaltijdpakket
bestellen. Een admin beheert de recepten en de bestellingen.

## Wat kan wie?

| Rol | Kan |
| --- | --- |
| Bezoeker (niet ingelogd) | Recepten, thema's en het weekmenu bekijken. Bij **Bestellen** word je naar de inlogpagina gestuurd. |
| Gebruiker | Inloggen of registreren en een recept bestellen. Gebruikers kunnen **geen** recepten toevoegen. |
| Admin | Recepten toevoegen, aanpassen en verwijderen, en bestellingen bekijken en verwijderen (pagina **Admin**). |

Het bestelformulier controleert de invoer:

- **Telefoonnummer:** Nederlands nummer, bijvoorbeeld `06 12345678`, `010-1234567` of `+31 6 12345678`.
- **Adres:** aparte velden voor straat, huisnummer (alleen cijfers), toevoeging (optioneel), postcode (`3011 AA`) en plaats.

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
