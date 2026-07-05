<?php
declare(strict_types=1);

namespace Engine\Translation;

class Translator
{
    private array $translations = [];
    private string $locale;

    public function __construct(string $locale = 'pl')
    {
        $this->locale = $locale;
    }

    public function loadModule(string $modulePath): void
    {
        $file = $modulePath . '/translations/' . $this->locale . '.php';
        if (file_exists($file)) {
            $this->translations = array_merge(
                $this->translations,
                require $file
            );
        }
    }

    public function loadApp(string $appPath): void
    {
        // src/ nadpisuje tłumaczenia modułu
        $file = $appPath . '/translations/' . $this->locale . '.php';
        if (file_exists($file)) {
            $this->translations = array_merge(
                $this->translations,
                require $file
            );
        }
    }

    public function trans(string $key, array $params = []): string
    {
        $message = $this->translations[$key] ?? $key;

        foreach ($params as $placeholder => $value) {
            $message = str_replace('%' . $placeholder . '%', $value, $message);
        }

        return $message;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
        $this->translations = []; // reset, przeładuj
    }
}