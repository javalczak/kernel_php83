# Pure Kernel

Autorski micro-framework PHP 8.3 pod wdrożenie na **shared hostingu przez FTP** — bez Composera na serwerze, bez SSH, bez Dockera.

## Dlaczego powstał

Symfony i Laravel zakładają, że na serwerze da się odpalić `composer install`. Na najtańszych hostingach współdzielonych nie ma ani konsoli, ani SSH — jest FTP i panel. Pure Kernel rozwiązuje ten problem wprost: wrzucasz pliki, uzupełniasz dane bazy i strona działa. Żadnego kroku instalacyjnego po stronie serwera.

Drugi powód jest taki, że chciałem mieć szkielet, w którym rozumiem każdą linię. Przy pięciu klasach silnika da się to ogarnąć w godzinę; przy Symfony nie da się wcale.

## Wymagania

- PHP 8.3 lub nowszy
- MySQL 5.7+ / MariaDB
- Rozszerzenie `pdo_mysql`

Composer nie jest potrzebny — ani lokalnie, ani na serwerze.

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

Wejdź na `http://localhost:8000` — strona główna i działający CRUD pod `/items`.

## Architektura

Front controller + MVC, dziewięć klas silnika i jeden interfejs, zero zewnętrznych zależności.

```
Żądanie → index.php (front controller)
             ↓
         Kernel::handle()
             ↓
         Router::match()        ← dopasowanie {parametrów}, nazwy tras przez preg_quote
             ↓
         Controller::akcja()
             ↓                  ← Repository → Entity (obiekty, nie tablice)
         Response::send()
```

### Klasy silnika (`engine/`)

| Klasa | Odpowiedzialność |
|---|---|
| `Kernel` | Bootstrap, serwisy, dopasowanie trasy i wywołanie kontrolera |
| `Router` | Rejestracja i dopasowanie tras z `{parametrami}` |
| `Request` | Opakowanie na `$_GET`, `$_POST`, `$_FILES`, `$_SERVER`, `$_COOKIE` + `string()` i `int()` |
| `Response` | Budowanie odpowiedzi HTTP, domyślnie `text/html; charset=utf-8` |
| `Controller` | Baza kontrolerów — `render()`, `redirect()`, `json()`, `db()`, `trans()` |
| `Database` | PDO: `fetchAll`, `fetchOne`, `insert`, `update`, `delete`, `transaction`, `identifier` |
| `Repository` | Baza repozytoriów: CRUD na encjach dla jednej tabeli |
| `Entity` | Interfejs encji: `fromRow()`, `toRow()`, `getId()`, `withId()` |
| `ServiceContainer` | Prosty kontener — `bind()` (za każdym razem nowy) i `singleton()` |
| `Translator` | Tłumaczenia z `%placeholderami%`, język z ciasteczka `locale` |

### Autoloader

Własny, bez Composera. `autoload.php` mapuje dwa namespace'y:

```
Engine\  →  engine/
App\     →  src/
```

## Encje

Repozytorium nie oddaje tablic z bazy, tylko obiekty. Zamiast `$item['title']`, gdzie literówka wychodzi dopiero na produkcji, jest `$item->title`, które sprawdza PHP i podpowiada IDE.

Encja to zwykła klasa z dwiema metodami mapującymi — bez ORM-a, bez refleksji, bez adnotacji:

```php
final class Example implements Entity
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $title,
        public readonly bool $isActive = true,
        public readonly ?string $createdAt = null,
    ) {}

    public static function fromRow(array $row): static
    {
        return new self((int) $row['id'], (string) $row['title'], (bool) $row['is_active'], $row['created_at'] ?? null);
    }

    public function toRow(): array
    {
        return ['title' => $this->title, 'is_active' => (int) $this->isActive];
    }
}
```

Właściwości są `readonly`, więc encja po zbudowaniu się nie zmienia. Zmiana to nowy obiekt (`withTitle()`, `withId()`) — nie trzeba szukać, kto podmienił pole w połowie żądania.

Repozytorium konkretnej tabeli podaje tylko nazwę tabeli i klasę encji, resztę dziedziczy:

```php
final class ExampleRepository extends Repository
{
    protected function table(): string { return ExampleSchema::TABLE; }
    protected function entityClass(): string { return Example::class; }

    public function findActive(): array
    {
        return $this->findBy(['is_active' => 1], 'created_at DESC');
    }
}
```

Z klasy bazowej dostajesz `findAll()`, `findById()`, `findBy()`, `findOneBy()`, `count()`, `save()` i `delete()`. `save()` sam decyduje między INSERT a UPDATE na podstawie `id` encji.

## Trasy

Trasy to tablica w `config/module.php`:

```php
['method' => 'GET',  'path' => '/items/{id}',        'controller' => ExampleController::class, 'action' => 'show'],
['method' => 'POST', 'path' => '/items/{id}/delete', 'controller' => ExampleController::class, 'action' => 'delete'],
```

Pierwsza pasująca trasa wygrywa, więc `/items/create` musi stać przed `/items/{id}`. Parametry `{id}` trafiają do metody kontrolera jako `$params`.

Aplikacja może dorzucić własny plik `src/routes.php` w tym samym formacie, żeby nie puchł główny config.

## Przykładowa aplikacja (`src/`, `templates/`)

W repozytorium jest kompletny, działający CRUD: lista z wyszukiwarką, podgląd, dodawanie z walidacją i usuwanie. Pokazuje wszystkie warstwy naraz:

- **Entity** (`src/Entity/Example.php`) — wiersz jako obiekt
- **Repository** (`src/Repository/ExampleRepository.php`) — zapytania do jednej tabeli
- **Service** (`src/Service/ExampleService.php`) — reguły domenowe i przykład transakcji
- **Schema** (`src/Schema/`) — DDL jako kod PHP, instalowalny z przeglądarki
- **Fixtures** (`src/Fixtures/AppFixtures.php`) — dane startowe dla deva
- **Security** (`src/Security/Csrf.php`) — token CSRF dla formularzy

Przykłady są celowo kompletne: łatwiej skopiować działający wzorzec niż odtwarzać go z dokumentacji.

## Bezpieczeństwo

Co jest wbudowane w szkielet:

- **SQL**: PDO z `ATTR_EMULATE_PREPARES = false` — wartości nigdy nie są częścią zapytania. Nazwy tabel i kolumn, których nie da się przekazać przez parametr, przechodzą przez `Database::identifier()` (biała lista znaków + backticki). `ORDER BY` jest walidowany osobno. `UPDATE` i `DELETE` bez `WHERE` rzucają wyjątkiem.
- **CSRF**: token sesyjny porównywany przez `hash_equals()`, wymagany w każdym formularzu POST. Usuwanie wyłącznie POST-em.
- **Sesja**: `HttpOnly`, `SameSite=Lax`, `secure` po HTTPS, czas życia 96 minut.
- **Nagłówki**: `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: same-origin`.
- **Ścieżki**: język z ciasteczka ograniczony białą listą (bez tego ciasteczko `locale` wczytywało dowolny plik PHP), nazwy szablonów walidowane, serwer deweloperski oddaje pliki tylko z `assets/` i `tools/`.
- **XSS**: wszystkie dane w szablonach idą przez `htmlspecialchars()`.

Czego tu nie ma, a bywa potrzebne: logowania i haseł, uploadu plików, limitów na liczbę żądań. To dokłada się per projekt.

## Wdrożenie na shared hosting

1. Wgraj przez FTP całość **oprócz** `tools/` i `router.php`.
2. Uzupełnij `config/database.php` danymi z panelu hostingu.
3. Upewnij się, że `.htaccess` wjechał razem z resztą (bywa ukryty w kliencie FTP) — to on kieruje cały ruch do `index.php` i blokuje dostęp do `config/`, `engine/`, `src/` i `templates/`.
4. Tabele załóż raz, lokalnie, i wyeksportuj dumpa — albo wgraj `tools/` na chwilę, odpal reset i skasuj katalog.

## Narzędzia deweloperskie (`tools/`)

```bash
php tools/reset-db.php --force   # drop + create + fixtures
php tools/smoke-test.php         # 24 sprawdzenia encji, repozytorium i walidacji
```

`reset-db.php` działa też z przeglądarki (`/tools/reset-db.php?token=…`), ale tylko z localhosta i tylko z tokenem z konfiguracji. Na produkcję tego katalogu się nie wgrywa.

`smoke-test.php` to świadomie proste rozwiązanie: projekt nie używa Composera, więc nie ma w nim PHPUnita. Testy robią przejście przez prawdziwą bazę i sprzątają po sobie.
