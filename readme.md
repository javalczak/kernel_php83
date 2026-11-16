# Pure Kernel

Micro-framework PHP 8.3 pod shared hosting. Bez Composera na serwerze, bez SSH, bez Dockera.

## Dlaczego powstał

Symfony i Laravel zakładają, że na serwerze da się odpalić `composer install`. Na najtańszych hostingach współdzielonych nie ma ani konsoli, ani SSH, jest FTP i panel. Tutaj wrzucasz pliki, uzupełniasz dane bazy i strona działa.

Drugi powód jest prostszy: chciałem raz napisać routing i kontener sam, żeby wiedzieć, co siedzi pod spodem.

## Wymagania

- PHP 8.3 lub nowszy
- MySQL 5.7+ / MariaDB
- Rozszerzenie `pdo_mysql`

Composer nie jest potrzebny.

## Szybki start

```bash
git clone git@github.com:javalczak/kernel_php83.git
cd kernel_php83
```

Uzupełnij `config/database.php`, potem:

```bash
php tools/reset-db.php --force     # zakłada bazę, tabele i dane startowe
php -S localhost:8000 router.php
```

`http://localhost:8000`, CRUD pod `/items`.

## Architektura

Front controller + MVC, dziewięć klas silnika i jeden interfejs, zero zależności.

```
Żądanie → index.php (front controller)
             ↓
         Kernel::handle()
             ↓
         Router::match()
             ↓
         Controller::akcja()
             ↓              ← Repository → Entity
         Response::send()
```

### Klasy silnika (`engine/`)

| Klasa | Odpowiedzialność |
|---|---|
| `Kernel` | Bootstrap, serwisy, dopasowanie trasy i wywołanie kontrolera |
| `Router` | Rejestracja i dopasowanie tras z `{parametrami}` |
| `Request` | Opakowanie na `$_GET`, `$_POST`, `$_FILES`, `$_SERVER`, `$_COOKIE` |
| `Response` | Budowanie odpowiedzi HTTP |
| `Controller` | Baza kontrolerów: `render()`, `redirect()`, `json()`, `db()`, `trans()` |
| `Database` | PDO: `fetchAll`, `fetchOne`, `insert`, `update`, `delete`, `transaction`, `identifier` |
| `Repository` | Baza repozytoriów, CRUD na encjach dla jednej tabeli |
| `Entity` | Interfejs encji: `fromRow()`, `toRow()`, `getId()`, `withId()` |
| `ServiceContainer` | `bind()` i `singleton()` |
| `Translator` | Tłumaczenia z `%placeholderami%`, język z ciasteczka `locale` |

### Autoloader

Własny, bez Composera. `autoload.php` mapuje dwa namespace'y:

```
Engine\  →  engine/
App\     →  src/
```

## Trasy

Trasy to tablica w `config/module.php`:

```php
['method' => 'GET',  'path' => '/items/{id}',        'controller' => ExampleController::class, 'action' => 'show'],
['method' => 'POST', 'path' => '/items/{id}/delete', 'controller' => ExampleController::class, 'action' => 'delete'],
```

Pierwsza pasująca trasa wygrywa, więc `/items/create` musi stać przed `/items/{id}`. Parametry `{id}` trafiają do metody kontrolera jako `$params`.

Aplikacja może dorzucić własny `src/routes.php` w tym samym formacie.

## Przykładowa aplikacja (`src/`, `templates/`)

Kompletny CRUD: lista z wyszukiwarką, podgląd, dodawanie z walidacją, usuwanie.

- Entity (`src/Entity/Example.php`)
- Repository (`src/Repository/ExampleRepository.php`)
- Service (`src/Service/ExampleService.php`), reguły domenowe i transakcja
- Schema (`src/Schema/`), DDL jako kod PHP
- Fixtures (`src/Fixtures/AppFixtures.php`)
- Security (`src/Security/Csrf.php`)

## Bezpieczeństwo

PDO z `ATTR_EMULATE_PREPARES = false`, więc wartości nigdy nie są częścią zapytania. Nazw tabel i kolumn parametrem się nie przekaże, te idą przez `Database::identifier()`: biała lista znaków plus backticki. `ORDER BY` ma osobną walidację. `UPDATE` i `DELETE` bez `WHERE` rzucają wyjątkiem.

Token CSRF siedzi w sesji, porównywany przez `hash_equals()`, wymagany w każdym POST. Usuwanie tylko POST-em.

Sesja: `HttpOnly`, `SameSite=Lax`, `secure` po HTTPS, 96 minut. Nagłówki: `nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: same-origin`. W szablonach wszystko idzie przez `htmlspecialchars()`.

Język z ciasteczka ograniczony białą listą, bez tego `locale` wczytywało dowolny plik PHP z dysku. Nazwy szablonów walidowane. Serwer deweloperski oddaje pliki tylko z `assets/` i `tools/`.

Nie ma tu logowania, uploadu plików ani limitów na liczbę żądań. Dokładam per projekt.

## Wdrożenie na shared hosting

1. Wgraj przez FTP całość oprócz `tools/` i `router.php`.
2. Uzupełnij `config/database.php` danymi z panelu.
3. Sprawdź, czy `.htaccess` wjechał, bo bywa ukryty w kliencie FTP. To on kieruje ruch do `index.php` i blokuje `config/`, `engine/`, `src/` i `templates/`.
4. Tabele załóż lokalnie i wyeksportuj dumpa. Albo wgraj `tools/` na chwilę, odpal reset i skasuj katalog.

## Narzędzia (`tools/`)

```bash
php tools/reset-db.php --force   # drop + create + fixtures
php tools/smoke-test.php         # 24 sprawdzenia encji, repozytorium i walidacji
```

`reset-db.php` chodzi też z przeglądarki (`/tools/reset-db.php?token=...`), ale tylko z localhosta i tylko z tokenem z konfiguracji. Na produkcję tego katalogu się nie wgrywa.

Bez Composera nie ma PHPUnita, więc `smoke-test.php` jest ręczny. Jedzie po prawdziwej bazie i sprząta po sobie.
