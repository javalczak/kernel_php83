<?php
declare(strict_types=1);

namespace App\Security;

/**
 * Token CSRF trzymany w sesji.
 *
 * Chroni przed tym, żeby obca strona wysłała formularz w imieniu zalogowanego
 * użytkownika: jej kod nie zna tokenu z naszej sesji, więc verify() odrzuci żądanie.
 * Każdy formularz POST w aplikacji musi mieć pole csrf_token.
 */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            // 32 bajty z random_bytes, nie rand() — to ma być nie do odgadnięcia
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function verify(?string $token): bool
    {
        // hash_equals porównuje w stałym czasie, czyli nie zdradza,
        // ile pierwszych znaków atakujący trafił
        return is_string($token)
            && $token !== ''
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}
