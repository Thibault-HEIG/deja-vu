<?php
require_once __DIR__ . '/../src/constants.php';

function getInputErrors(array|string $upload, string $tmpFile, string $category, int $season_id, int $hue, float $saturation, float $brightness, int $rate): array {
    $errors = array_merge(
        getImgInputErrors($upload, $tmpFile),
        getCategoryInputErrors($category),
        getSeasonInputErrors($season_id),
        getColorInputErrors($hue, $saturation, $brightness),
        getRateInputErrors($rate)
    );
    return $errors;
}

function getImgInputErrors(array|string $upload, string $tmpFile = ''): array {
    // 1. Image processed by ClearBackdrop API
    $tmpFilename = basename($tmpFile);
    $tmpPath     = __DIR__ . '/../public/img/tmp/' . $tmpFilename;

    if ($tmpFilename !== '' && file_exists($tmpPath)) {
        return [];
    }

    if (is_string($upload)) {
        $path = __DIR__ . '/../public/' . $upload;
        return ($upload !== '' && file_exists($path)) ? [] : ["Format de photo non accepté ou erreur lors du téléchargement."];
    }

    // 2. Fallback: raw image uploaded through form
    if (empty($upload['tmp_name']) || $upload['error'] !== UPLOAD_ERR_OK) {
        return ["Format de photo non accepté ou erreur lors du téléchargement."];
    }

    $mime = mime_content_type($upload['tmp_name']);
    if (!array_key_exists($mime, ALLOWED_IMAGE_TYPES)) {
        return ["Format de photo non accepté ou erreur lors du téléchargement."];
    }

    return [];
}

function getColorInputErrors(int $hue, float $saturation, float $brightness): array {
    // Par défaut, il n'y a pas d'erreurs
    $errors = [];
    if ($hue < LIMITS['h'][0] || $hue > LIMITS['h'][1]) {
        $errors[] = "La valeur de teinte n'est pas valide.";
    }
    if ($saturation < LIMITS['s'][0] || $saturation > LIMITS['s'][1]) {
        $errors[] = "La valeur de saturation n'est pas valide.";
    }
    if ($brightness < LIMITS['v'][0] || $brightness > LIMITS['v'][1]) {
        $errors[] = "La valeur de luminosité n'est pas valide.";
    }
    return $errors;
}

function getCategoryInputErrors(string $category): array {
    if (!array_key_exists($category, CATEGORIES)) {
        return ["La catégorie sélectionnée n'est pas valide."];
    } else {
        return [];
    }
}

function getSeasonInputErrors(int $season_id): array {
    if ($season_id < 1 || $season_id > 4) {
        return ["La saison sélectionnée n'est pas valide."];
    } else {
        return [];
    }
}

function getRateInputErrors(int $rate): array {
    if ($rate < 1 || $rate > 5) {
        return ["La note sélectionnée n'est pas entre 1 et 5."];
    } else {
        return [];
    }
}
