# Pure Kernel

Autorski micro-framework PHP 8.3 zaprojektowany pod wdrożenie na **shared hostingu przez FTP** — bez Composera na serwerze, bez SSH, bez Dockera.

## Dlaczego powstał

Popularne frameworki PHP (Symfony, Laravel) zakładają dostęp do terminala i możliwość uruchomienia `composer install` na serwerze. Na najtańszych hostingach współdzielonych tego nie ma — jest tylko FTP i panel cPanel. Pure Kernel rozwiązuje ten problem: cały kod działa od razu po wrzuceniu plików, bez żadnego kroku instalacji po stronie serwera.

## Wymagania

- PHP 8.3+
- MySQL
- Rozszerzenia PHP: `pdo_mysql`, `fileinfo`

Composer nie jest wymagany.

## Architektura

Front Controller + MVC, ~7 klas silnika, zero zewnętrznych zależności.

```
Request → Kernel::handle()
              ↓
          Router::match()       ← dopasowanie {parametrów} przez regex
              ↓
          Controller::action()
              ↓
          Response::send()
```

### Klasy silnika (`engine/`)

| Klasa | Odpowiedzialność |
|---|---|
| `Kernel` | Bootstrap, wstrzykiwanie serwisów, obsługa żądania |
| `Router` | Rejestracja i dopasowywanie tras z `{parametrami}` |
| `Request` | Immutable wrapper na `$_GET`, `$_POST`, `$_FILES`, `$_SERVER`, `$_COOKIE` |
| `Response` | Fluent builder odpowiedzi HTTP |
| `Controller` | Abstrakcyjna baza — `render()`, `redirect()`, `db()`, `trans()` |
| `Database` | Wrapper PDO: `fetchAll`, `fetchOne`, `insert`, `update`, `delete`, `transaction` |
| `ServiceContainer` | Prosty IoC container — `bind()` (transient) i `singleton()` |
| `Translator` | Tłumaczenia z `%placeholderami%`, obsługa nadpisywania przez aplikację |

### Autoloader

Własna implementacja PSR-style bez Composera — `autoload.php` mapuje dwa namespace'y:

```
Engine\  →  engine/
App\     →  src/
```

## Struktura projektu

```
engine/          ← silnik frameworka
config/          ← konfiguracja bazy danych i tras
src/             ← kod referencyjny aplikacji (patrz niżej)
templates/       ← szablony PHP aplikacji referencyjnej
tools/           ← narzędzia deweloperskie (reset bazy)
index.php        ← entry point
autoload.php     ← autoloader (bez Composera)
router.php       ← router dla wbudowanego serwera PHP (dev only)
```

## Konfiguracja

Skopiuj `config/database.example.php` do `config/database.php` i uzupełnij dane połączenia:

```bash
cp config/database.example.php config/database.php
```

```php
return [
    'host'     => 'localhost',
    'dbname'   => 'nazwa_bazy',
    'user'     => 'uzytkownik',
    'password' => 'haslo',
    'charset'  => 'utf8mb4',
];
```

## Uruchomienie lokalne

```bash
php -S localhost:8000 router.php
```

## Trasy

Trasy definiuje się w `config/module.php` jako tablica asocjacyjna:

```php
['method' => 'GET',  'path' => '/listing/{slug}', 'controller' => ListingController::class, 'action' => 'show'],
['method' => 'POST', 'path' => '/listing/{slug}', 'controller' => ListingController::class, 'action' => 'update'],
```

Parametry `{slug}` są automatycznie wyodrębniane i przekazywane do metody kontrolera.

## Przykład kontrolera

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Engine\Controller;
use Engine\Request;
use Engine\Response;

class ListingController extends Controller
{
    public function show(Request $request, array $params): Response
    {
        $slug    = $params['slug'];
        $listing = $this->db()->fetchOne('SELECT * FROM properties WHERE slug = ?', [$slug]);

        if ($listing === null) {
            return Response::notFound();
        }

        return $this->render(BASE_PATH . '/templates/listing/show', [
            'listing' => $listing,
        ]);
    }
}
```

## Bezpieczeństwo

Wbudowane w szkielet frameworka:

- Nagłówki HTTP: `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: same-origin`
- Sesja: `HttpOnly`, `SameSite=Lax`, czas życia 96 min
- Baza danych: PDO z `ATTR_EMULATE_PREPARES = false` i `ERRMODE_EXCEPTION`

Wzorce bezpieczeństwa w kodzie referencyjnym (`src/`):

- Hasła: `password_hash()` z `PASSWORD_BCRYPT`, cost 12, timing-safe porównanie
- CSRF: tokeny sesyjne weryfikowane przez `hash_equals()`
- Upload plików: walidacja MIME przez `finfo`, whitelist rozszerzeń, losowe nazwy plików, limit 8 MB

## Kod referencyjny (`src/`)

Katalog `src/`, `templates/` oraz `config/module.php` zawierają **kod referencyjny** z aplikacji *Fiestalettings* — platformy bezpośredniego wynajmu nieruchomości wakacyjnych (Cypr). Nie jest to działająca, kompletna aplikacja, ale przykład wzorców stosowanych podczas budowania na tym silniku.

Wzorce zastosowane w kodzie referencyjnym:

- **Repository** — jeden obiekt na tabelę, zapytania przez PDO z prepared statements
- **Schema** — definicje DDL jako klasy PHP, `SchemaInstaller` z określoną kolejnością instalacji i seedowania
- **Service** — wydzielona logika współdzielona między kontrolerami (np. `PhotoUploader`)
- **Fixtures** — seeder bazy danych dla środowiska dev/demo

## Narzędzia deweloperskie

`tools/reset-db.php` — przeglądarkowe narzędzie do resetowania bazy danych (drop + recreate + seed). Dostęp chroniony tokenem z `config/database.php`. Przeznaczone wyłącznie do użytku lokalnego.
