<?php
declare(strict_types=1);

/**
 * Funkcje globalne silnika. Na razie jedna: dd() do debugowania.
 */

if (!function_exists('dd')) {
    /**
     * "Dump and die" - pokazuje zmienne i przerywa wykonanie.
     *
     * Tylko do pracy lokalnej. Na produkcji taki zrzut wyświetliłby zawartość
     * obiektów (a w nich potrafi siedzieć hasło z konfiguracji) obcej osobie.
     */
    function dd(mixed ...$vars): never
    {
        http_response_code(500);

        echo <<<HTML
            <!DOCTYPE html>
            <html lang="pl">
            <head>
                <meta charset="utf-8">
                <title>Dump</title>
                <style>
                    body { margin: 0; background: #18171B; color: #F8F8F2; font-family: Consolas, Monaco, monospace; }
                    .dump-container { padding: 20px; }
                    .dump-block { background: #272822; border-left: 5px solid #A6E22E; padding: 15px; margin-bottom: 10px; overflow: auto; line-height: 1.5; font-size: 14px; }
                </style>
            </head>
            <body>
            <div class="dump-container">
            HTML;

        foreach ($vars as $var) {
            echo '<div class="dump-block"><pre>';
            // var_export + highlight_string daje kolorowanie składni za darmo
            highlight_string("<?php\n" . var_export($var, true));
            echo '</pre></div>';
        }

        echo '</div></body></html>';

        exit;
    }
}
