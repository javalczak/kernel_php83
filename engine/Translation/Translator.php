<?php
declare(strict_types=1);

namespace Engine\Translation;

class Translator
{
    // Locale trafia tu z ciasteczka, a niżej ląduje w require. Bez tej listy
    // wystarczyłoby ciasteczko locale=../../config/database, żeby wykonać cudzy plik.
    private const DOSTEPNE = ['pl', 'en'];

    private const DOMYSLNY = 'en';

    private array $translations = [];
    private string $locale;

    public function __construct(string $locale = self::DOMYSLNY)
    {
        $this->locale = self::czyste($locale);
    }

    /**
     * Wczytuje translations/{locale}.php z podanego katalogu.
     * Można wołać kilka razy - późniejsze klucze nadpisują wcześniejsze.
     */
    public function load(string $path): void
    {
        $file = $path . '/translations/' . $this->locale . '.php';

        if (!is_file($file)) {
            return;
        }

        $translations = require $file;

        if (is_array($translations)) {
            $this->translations = array_merge($this->translations, $translations);
        }
    }

    public function trans(string $key, array $params = []): string
    {
        // Brak klucza = zwracamy sam klucz. Lepiej zobaczyć "example.index.title"
        // na stronie niż pustkę, od razu wiadomo czego brakuje w pliku tłumaczeń.
        $message = $this->translations[$key] ?? $key;

        foreach ($params as $placeholder => $value) {
            $message = str_replace('%' . $placeholder . '%', (string) $value, $message);
        }

        return $message;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = self::czyste($locale);
        $this->translations = []; // reset, przeładuj
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /** Nieznany język cicho wraca do domyślnego - strona ma działać, nie wybuchać. */
    private static function czyste(string $locale): string
    {
        return in_array($locale, self::DOSTEPNE, true) ? $locale : self::DOMYSLNY;
    }
}
