<?php

class Color {
    private int $hue;
    private int $saturation;
    private int $brightness;

    public function __construct(int $hue, int $saturation, int $brightness) {
        if ($hue < 0 || $hue > 360) {
            throw new InvalidArgumentException("Hue must be between 0 and 360.");
        }
        if ($saturation < 0 || $saturation > 100) {
            throw new InvalidArgumentException("Saturation must be between 0 and 100.");
        }
        if ($brightness < 0 || $brightness > 100) {
            throw new InvalidArgumentException("Brightness must be between 0 and 100.");
        }
        $this->hue = $hue;
        $this->saturation = $saturation;
        $this->brightness = $brightness;
    }
    public function getHue(): int {
        return $this->hue;
    }

    public function getSaturation(): int {
        return $this->saturation;
    }

    public function getBrightness(): int {
        return $this->brightness;
    }

    /**
     * Determines whether this color is neutral (grayscale/black/white/muted)
     * based on the boundary curve from tests/rating-app.
     *
     * @return bool True if on or below the curve (neutral), false if colorful.
     */
    public function isNeutral(): bool {
        $s = $this->saturation / 100.0;
        $b = $this->brightness / 100.0;

        $slope = 4.850;
        $floor = 0.129;
        $k = 4.0;

        $x = $k * (1.0 - $slope * $s - $floor);
        // Numerically stable softplus: max(x, 0) + log(1 + exp(-abs(x)))
        $softplus = max($x, 0.0) + log1p(exp(-abs($x)));
        $curveBrightness = $floor + $softplus / $k;

        return $b <= $curveBrightness;
    }
}
