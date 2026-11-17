<?php

class ErrorHandler {

    /**
     * Must be called on top of every public/ php file
     */
    public static function register(): void {
        $isDev = getenv('APP_ENV') === 'dev';

        error_reporting(E_ALL);
        ini_set('display_errors', $isDev ? '1' : '0'); // dev: errors displayed, prod: errors replaced by a generic one

        if (!$isDev) {
            set_exception_handler(function (Throwable $e) {
                error_log((string) $e); // write in Infomaniak server logs for debuging
                http_response_code(500);
                echo 'Une erreur inattendue est survenue. Veuillez recharger la page.'; // basic message to avoid leaking architecture.
            });
        }
    }

    /**
     * Display form errors
     */
    public static function displayErrors(array $errors): void {
        if (!empty($errors)) {
            echo "<p>Le formulaire contient des erreurs :</p>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul>";
        }
    }
}
