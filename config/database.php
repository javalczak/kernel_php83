<?php
declare(strict_types=1);

/**
 * Konfiguracja bazy. W repozytorium leżą wartości przykładowe -
 * po sklonowaniu podmień je na swoje i NIE commituj prawdziwych haseł.
 */
return [
    'host'     => 'localhost',
    'dbname'   => 'kernel_php83',
    'user'     => 'root',
    'password' => 'root',
    'charset'  => 'utf8mb4',

    // Token do tools/reset-db.php w przeglądarce. Z konsoli nie jest potrzebny.
    // Wygeneruj swój: php -r "echo bin2hex(random_bytes(24));"
    'reset_token' => 'change-me-to-a-long-random-string',
];
