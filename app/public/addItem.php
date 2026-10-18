<?php
require_once __DIR__ . '/../src/renderer.php';
require_once __DIR__ . '/../src/itemRepository.php';
require_once __DIR__ . '/../src/constants.php';

// Convertit une couleur hexadécimale (#rrggbb) en teinte / saturation / luminosité
function hexToHsv(string $hex): array {
    $r = hexdec(substr($hex, 1, 2)) / 255;
    $g = hexdec(substr($hex, 3, 2)) / 255;
    $b = hexdec(substr($hex, 5, 2)) / 255;

    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $delta = $max - $min;

    // Teinte
    if ($delta == 0) {
        $hue = 0;
    } elseif ($max == $r) {
        $hue = 60 * fmod(($g - $b) / $delta, 6);
    } elseif ($max == $g) {
        $hue = 60 * (($b - $r) / $delta + 2);
    } else {
        $hue = 60 * (($r - $g) / $delta + 4);
    }
    if ($hue < 0) {
        $hue += 360;
    }

    // Saturation et luminosité (entre 0 et 1)
    $saturation = $max == 0 ? 0 : $delta / $max;
    $brightness = $max;

    return [$hue, $saturation, $brightness];
}

$currentUserId = 1; // ID de l'utilisateur actuel (à remplacer par la session plus tard)

// Catégories autorisées : valeur en base => libellé affiché
$categories = [
    'top'       => 'Haut',
    'bottom'    => 'Bas',
    'jacket'    => 'Veste',
    'shoes'     => 'Chaussures',
    'accessory' => 'Accessoire',
];

// Types d'images autorisés : type MIME => extension
$allowedImageTypes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

$seasons = getAllSeasons();
$seasonIds = [];
foreach ($seasons as $season) {
    $seasonIds[] = $season->getId();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'] ?? '';
    $seasonId = (int) ($_POST['season_id'] ?? 0);
    $color    = $_POST['color'] ?? '';
    $rate     = (int) ($_POST['rate'] ?? 0);
    $photo    = $_FILES['photo'] ?? null;

    // Vérifications
    if (!array_key_exists($category, $categories)) {
        $errors[] = 'Type invalide.';
    }
    if (!in_array($seasonId, $seasonIds, true)) {
        $errors[] = 'Saison invalide.';
    }
    if ($rate < 1 || $rate > 5) {
        $errors[] = 'Note invalide.';
    }

    // Couleur : on vérifie le format, puis on convertit
    if (strlen($color) !== 7 || $color[0] !== '#' || !ctype_xdigit(substr($color, 1))) {
        $errors[] = 'Couleur invalide.';
        $color = '#000000';
    }
    $hsv = hexToHsv($color);
    $hue = $hsv[0];
    $saturation = $hsv[1];
    $brightness = $hsv[2];

    // Clamp : on limite la saturation et la luminosité (valeur max à définir)
    $saturation = min($saturation, 1);
    $brightness = min($brightness, 1);

    // Photo
    $extension = null;
    if (!$photo || $photo['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Photo manquante ou envoi échoué.';
    } else {
        $mime = mime_content_type($photo['tmp_name']);
        if (!isset($allowedImageTypes[$mime])) {
            $errors[] = 'Format de photo non accepté (JPG, PNG ou WEBP).';
        } else {
            $extension = $allowedImageTypes[$mime];
        }
    }

    // Enregistrement
    if (empty($errors)) {
        $relativeDir = 'img/uploads/user_' . $currentUserId;
        $absoluteDir = __DIR__ . '/' . $relativeDir;

        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0775, true);
        }

        $fileName = uniqid('item_') . '.' . $extension;
        $imgUrl = $relativeDir . '/' . $fileName;

        if (move_uploaded_file($photo['tmp_name'], $absoluteDir . '/' . $fileName)) {
            addItem($imgUrl, $category, $seasonId, $hue, $saturation, $brightness, $rate, $currentUserId);
            header('Location: index.php#wardrobe');
            exit;
        }
        $errors[] = "Impossible d'enregistrer la photo.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<?php render('head', ['title' => 'Ajouter un vêtement', 'description' => 'Clothes Match - Ajouter un vêtement']); ?>

<body>
    <h1>Ajouter un vêtement</h1>

    <?php foreach ($errors as $error): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">

        <p>
            <label for="photo">Photo</label><br>
            <input type="file" id="photo" name="photo" accept="image/*" required>
        </p>

        <p>
            <label for="category">Type</label><br>
            <select id="category" name="category" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($categories as $value => $label): ?>
                    <option value="<?= $value ?>"><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            Saison<br>
            <?php foreach ($seasons as $season): ?>
                <label>
                    <input type="radio" name="season_id" value="<?= $season->getId() ?>" required>
                    <?= htmlspecialchars($season->getName()) ?>
                </label>
            <?php endforeach; ?>
        </p>

        <p>
            <label for="color">Couleur</label><br>
            <input type="color" id="color" name="color" value="#808080">
        </p>

        <p>
            Note<br>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <label>
                    <input type="radio" name="rate" value="<?= $i ?>" required> <?= $i ?>★
                </label>
            <?php endfor; ?>
        </p>

        <button type="submit">Ajouter</button>
    </form>
</body>

<?php render('footer'); ?>

</html>
