# DCTerra Livestream (Laravel)

Dit is de website van de **24 uurs livestream van DCTerra**. De website is eerst gemaakt in TypeScript / Next.js en is omgebouwd naar **PHP / Laravel**, zodat studenten van SD (Software Development) er zelf aan kunnen werken.

Met de website kunnen:

- **bezoekers** het evenement bekijken (aftellen, programma, sponsors, FAQ);
- **studenten** zich registreren voor de livestream en een Discord code per e-mail krijgen;
- **vrijwilligers en organisaties** zich aanmelden om te helpen;
- **beheerders** alles regelen in het admin panel, zonder code aan te passen.

## Gebruikte techniek

| Onderdeel | Techniek |
| --- | --- |
| Backend | [Laravel 13](https://laravel.com/docs) (PHP 8.3+) |
| Pagina's en admin panel | [Livewire 4](https://livewire.laravel.com/docs) (single-file components) |
| UI componenten | [Flux](https://fluxui.dev/docs) + eigen Blade componenten |
| Styling | [Tailwind CSS 4](https://tailwindcss.com/docs) |
| Kleine interacties (aftellen, menu) | [Alpine.js](https://alpinejs.dev/) (zit in Livewire) |
| Inloggen | [Laravel Fortify](https://laravel.com/docs/fortify) |
| Database | SQLite (lokaal), MySQL mag ook |
| Tests | [Pest](https://pestphp.com/docs) |

## Installeren

### Wat je nodig hebt

- PHP 8.3 of nieuwer (met de `sqlite` extensie)
- [Composer](https://getcomposer.org/)
- Node.js 22 of nieuwer + npm

Tip: met [Laravel Herd](https://herd.laravel.com/) heb je PHP en Composer in één keer geïnstalleerd.

### Stappen

```bash
# 1. Project ophalen
git clone https://github.com/GamerDev1180/dcterralivestream_php.git
cd dcterralivestream_php

# 2. Alles installeren (composer, .env, app key, database, npm, build)
composer run setup

# 3. Voorbeelddata en een admin account toevoegen
php artisan db:seed

# 4. Website starten
composer run dev
```

Open daarna http://localhost:8000.

> Krijg je bij stap 2 een foutmelding over de database? Maak dan eerst een leeg bestand `database/database.sqlite` aan en draai `php artisan migrate`.

### Inloggen in het admin panel

Ga naar http://localhost:8000/admin en log in met:

- **Gebruikersnaam:** `admin`
- **Wachtwoord:** `password`

Dit account wordt alleen gemaakt door de seeder en is bedoeld voor je eigen computer. Maak op een echte server een admin aan met:

```bash
php artisan app:create-admin
```

### Database opnieuw beginnen

```bash
php artisan migrate:fresh --seed
```

Let op: dit verwijdert **alle** data in je lokale database.

### E-mail

Lokaal staat `MAIL_MAILER=log` in `.env`. Verstuurde e-mails (zoals de registratiebevestiging) komen dan in `storage/logs/laravel.log` in plaats van in een echte mailbox. Voor een echte server vul je de `MAIL_*` instellingen in `.env` in.

## Waar staat wat?

```
app/
├── Actions/                 Losse taken, bv. RegisterParticipant en ArchiveAndResetEvent
├── Concerns/                Validatieregels die door meerdere plekken gebruikt worden
├── Console/Commands/        Artisan commands (app:create-admin)
├── Enums/                   Vaste keuzes, bv. statussen, sponsor niveaus, soorten vragen
├── Http/
│   ├── Controllers/Api/     De publieke API
│   ├── Controllers/Admin/   CSV export van registraties
│   ├── Requests/            Validatie van API verzoeken
│   └── Resources/           Hoe modellen als JSON teruggegeven worden
├── Mail/                    De registratiebevestiging
├── Models/                  Eloquent modellen (Registration, Sponsor, Setting, ...)
└── Rules/                   Eigen validatieregels (toegestane e-maildomeinen)

database/
├── factories/               Nep-data voor tests en de seeder
├── migrations/              De opbouw van de database
└── seeders/                 Standaard vragen, domeinen en voorbeelddata

resources/
├── css/app.css              Kleuren en glow-effecten van de website
└── views/
    ├── components/          Herbruikbare Blade componenten
    │   ├── ui/              Card, button, badge en alert
    │   └── admin/           De statistieken bovenaan het admin panel
    ├── layouts/             public.blade.php (website) en admin.blade.php (admin panel)
    ├── mail/                De HTML van de bevestigingsmail
    └── pages/               Alle pagina's (Livewire components)
        ├── ⚡home.blade.php
        ├── ⚡register.blade.php
        ├── ⚡schedule.blade.php
        ├── ⚡sponsors.blade.php
        ├── ⚡team.blade.php
        └── admin/           Eén bestand per tabblad van het admin panel

routes/
├── web.php                  Pagina's en admin panel
└── api.php                  Publieke API

tests/Feature/               Pest tests
```

### Hoe een pagina werkt

Elke pagina in `resources/views/pages` is een **Livewire single-file component**. Bovenaan staat de PHP class (data en acties), daaronder de HTML:

```blade
<?php
new class extends Component {
    public string $name = '';

    public function save(): void
    {
        // wordt uitgevoerd als je op de knop klikt
    }
}; ?>

<form wire:submit="save">
    <flux:input wire:model="name" label="Naam" />
    <flux:button type="submit">Opslaan</flux:button>
</form>
```

## Instellingen zonder code (admin panel)

Alles wat per jaar verandert, pas je aan in het admin panel. Deze instellingen worden opgeslagen in de `settings` tabel (zie `app/Models/Setting.php` voor alle sleutels en standaardwaarden).

| Tabblad | Wat je daar doet |
| --- | --- |
| Registrations | Registraties bekijken, bevestigen, annuleren, verwijderen en exporteren als CSV |
| Team Aanmeldingen | Vrijwilligers en organisaties goedkeuren of afwijzen |
| Schedule | Het programma van de livestream |
| Event Timing | Starttijd stream, einde registratie, titel en registratie open/dicht |
| Site Settings | Pagina's aan/uit zetten, organisatiegegevens en statistieken |
| Sponsors | Sponsors met niveau (Platinum, Gold, Silver, Bronze) |
| FAQ | Veelgestelde vragen op de homepagina |
| Email Domains | Welke e-maildomeinen mogen registreren (geen domeinen = niemand) |
| Questions | Extra vragen bij het registreren |
| Settings | Admins aanmaken en alles archiveren voor volgend jaar (alleen super admin) |

## API

De publieke API gebruikt dezelfde adressen en JSON als de oude Next.js versie.

| Methode | Adres | Wat het doet |
| --- | --- | --- |
| GET | `/api/event-config` | Alle instellingen van het evenement |
| GET | `/api/registration-status` | Is de registratie open? |
| GET | `/api/public/live-stats` | Aantal bevestigde deelnemers en opgehaald bedrag |
| GET | `/api/questions` | De registratievragen |
| GET | `/api/schedule-events` | Het programma |
| GET | `/api/sponsors` | De actieve sponsors |
| GET | `/api/faq` | De actieve FAQ items |
| POST | `/api/check-email` | Mag dit e-mailadres registreren en bestaat het al? |
| POST | `/api/register` | Registreren (`name`, `email`, `answers`) |
| POST | `/api/team-signup` | Aanmelden als vrijwilliger of organisatie |

Alles wat een login nodig heeft, zit in het Livewire admin panel en heeft geen API.

## Handige commando's

```bash
composer run dev                  # website starten (PHP server en Vite tegelijk)
npm run build                     # CSS en JavaScript bouwen
php artisan test --compact        # alle tests draaien
php artisan test --filter=Sponsor # alleen tests met "Sponsor" in de naam
vendor/bin/pint                   # PHP code netjes opmaken
composer run types:check          # PHPStan (code controleren op fouten)
php artisan route:list            # alle routes bekijken
php artisan app:create-admin      # admin account aanmaken of bijwerken
```

## Meewerken

1. Maak een nieuwe branch: `git checkout -b mijn-verbetering`
2. Schrijf of pas een test aan in `tests/Feature` voor wat je verandert.
3. Controleer of alles werkt: `php artisan test --compact` en `vendor/bin/pint`.
4. Maak een pull request.

Een paar afspraken:

- Gebruik `php artisan make:...` om nieuwe bestanden te maken (bv. `php artisan make:model`).
- Zet nooit wachtwoorden, tokens of andere geheimen in de code. Die horen in `.env`, en `.env` wordt niet meegestuurd naar GitHub.
- Kijk eerst of er al een component bestaat in `resources/views/components` voordat je een nieuwe maakt.
