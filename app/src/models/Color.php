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
}
