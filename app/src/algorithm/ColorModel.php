<?php
require_once __DIR__ . '/../models/Color.php';

/**
 * ColorModel — predicts color harmony score between two colors
 * using an RBF Kernel Ridge Regression model trained in Python.
 */
class ColorModel {
    private string $modelPath;
    private bool $isLoaded = false;
    private float $gamma;
    private float $intercept;
    private float $harmonySigma;
    private array $scalerMean = [];
    private array $scalerScale = [];
    private array $dualCoef = [];
    private array $xFit = [];

    public function __construct(?string $modelPath = null) {
        if ($modelPath === null || $modelPath === 'model.json' || !file_exists($modelPath)) {
            $this->modelPath = file_exists($modelPath ?? '') ? $modelPath : __DIR__ . '/model.json';
        } else {
            $this->modelPath = $modelPath;
        }
        $this->loadModel();
    }

    /**
     * Predicts the harmony score between two colors.
     * Score is strictly symmetric: predict(A, B) === predict(B, A).
     */
    public function predict(Color $color1, Color $color2): float {
        if (!$this->isLoaded) {
            throw new Exception("Le modèle n'est pas chargé.");
        }

        $features = $this->extractFeatures($color1, $color2);
        return $this->predictFromFeatures($features);
    }

    /**
     * Extracts 15 symmetric features from the two colors.
     * Order matches the training pipeline in pipeline.ipynb exactly.
     */
    public function extractFeatures(Color $color1, Color $color2): array {
        $h1 = $color1->getHue();
        $h2 = $color2->getHue();
        $s1 = $color1->getSaturation() / 100.0;
        $s2 = $color2->getSaturation() / 100.0;
        $b1 = $color1->getBrightness() / 100.0;
        $b2 = $color2->getBrightness() / 100.0;

        // Circular hue distance: min(|h1 - h2|, 360 - |h1 - h2|)
        $dh = abs($h1 - $h2);
        $hueDistance = min($dh, 360.0 - $dh);

        // Circular hue position sums
        $rad1 = deg2rad($h1);
        $rad2 = deg2rad($h2);
        $hueSinSum = sin($rad1) + sin($rad2);
        $hueCosSum = cos($rad1) + cos($rad2);
        $hueSimilarity = 1.0 - ($hueDistance / 180.0);

        // Smooth Gaussian color harmonies centered on theoretical angles
        $twoSigmaSq = 2.0 * $this->harmonySigma * $this->harmonySigma;
        $harmonyAnalogous     = exp(-pow($hueDistance - 0.0, 2) / $twoSigmaSq);
        $harmonySquare        = exp(-pow($hueDistance - 90.0, 2) / $twoSigmaSq);
        $harmonyTriadic       = exp(-pow($hueDistance - 120.0, 2) / $twoSigmaSq);
        $harmonySplitComp     = exp(-pow($hueDistance - 150.0, 2) / $twoSigmaSq);
        $harmonyComplementary = exp(-pow($hueDistance - 180.0, 2) / $twoSigmaSq);

        // Saturation & brightness statistics
        $satAvg  = ($s1 + $s2) / 2.0;
        $satDiff = abs($s1 - $s2);
        $satProd = $s1 * $s2;

        $briAvg  = ($b1 + $b2) / 2.0;
        $briDiff = abs($b1 - $b2);
        $briProd = $b1 * $b2;

        // Neutrals count (0, 1, or 2)
        $neutralCount = ($color1->isNeutral() ? 1.0 : 0.0) + ($color2->isNeutral() ? 1.0 : 0.0);

        return [
            $hueSimilarity,
            $hueSinSum,
            $hueCosSum,
            $harmonyAnalogous,
            $harmonySquare,
            $harmonyTriadic,
            $harmonySplitComp,
            $harmonyComplementary,
            $satAvg,
            $satDiff,
            $satProd,
            $briAvg,
            $briDiff,
            $briProd,
            $neutralCount,
        ];
    }

    /**
     * Standardizes features, computes RBF kernel sum, and clamps to [0.0, 1.0].
     */
    private function predictFromFeatures(array $features): float {
        $numFeatures = count($features);

        // Standardize features using the training set scaler
        $scaled = [];
        for ($j = 0; $j < $numFeatures; $j++) {
            $scale = ($this->scalerScale[$j] ?? 1.0);
            $mean = ($this->scalerMean[$j] ?? 0.0);
            $scaled[$j] = abs($scale) > 1e-9 ? ($features[$j] - $mean) / $scale : 0.0;
        }

        // RBF Kernel Ridge evaluation: y = intercept + sum_i (dual_coef_i * exp(-gamma * ||x - x_i||^2))
        $y = $this->intercept;
        $numSamples = count($this->xFit);

        for ($i = 0; $i < $numSamples; $i++) {
            $supportVector = $this->xFit[$i];
            $distSq = 0.0;
            for ($j = 0; $j < $numFeatures; $j++) {
                $diff = $scaled[$j] - $supportVector[$j];
                $distSq += $diff * $diff;
            }
            $k = exp(-$this->gamma * $distSq);
            $y += $this->dualCoef[$i] * $k;
        }

        return max(0.0, min(1.0, $y));
    }

    /**
     * Loads the model weights from the JSON file.
     */
    private function loadModel(): void {
        if (!file_exists($this->modelPath)) {
            $this->isLoaded = false;
            return;
        }

        $json = file_get_contents($this->modelPath);
        if ($json === false) {
            $this->isLoaded = false;
            return;
        }

        $data = json_decode($json, true);
        if (
            !is_array($data) ||
            !isset($data['gamma'], $data['intercept'], $data['scaler_mean'], $data['scaler_scale'], $data['dual_coef'], $data['X_fit'])
        ) {
            $this->isLoaded = false;
            return;
        }

        $this->gamma = (float)$data['gamma'];
        $this->intercept = (float)$data['intercept'];
        $this->harmonySigma = isset($data['harmony_sigma']) ? (float)$data['harmony_sigma'] : 15.0;
        $this->scalerMean = $data['scaler_mean'];
        $this->scalerScale = $data['scaler_scale'];
        $this->dualCoef = $data['dual_coef'];
        $this->xFit = $data['X_fit'];
        $this->isLoaded = true;
    }

    public function isLoaded(): bool {
        return $this->isLoaded;
    }
}
