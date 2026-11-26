<?php

class ErrorHandler {

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
