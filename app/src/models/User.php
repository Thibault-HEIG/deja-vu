<?php

/**
 * User class representing a user.
 */

class User {
    private ?int $id;
    private string $name;
    private float $last_color_harmony_importance;
    private float $last_season_harmony_importance;
    private string $secretToken;

    public function __construct(string $name, float $last_color_harmony_importance = 0.5, float $last_season_harmony_importance = 0.5, ?int $id = null, string $secretToken = '') {
        if ($last_color_harmony_importance < 0 || $last_color_harmony_importance > 1) {
            throw new InvalidArgumentException("Color harmony importance must be between 0 and 1.");
        }
        if ($last_season_harmony_importance < 0 || $last_season_harmony_importance > 1) {
            throw new InvalidArgumentException("Season harmony importance must be between 0 and 1.");
        }
        if (1 - ($last_color_harmony_importance + $last_season_harmony_importance) > 0.001) { // Allowing a small margin for floating-point comparison
            throw new InvalidArgumentException("The sum of color and season harmony importance must be 1.");
        }
        $this->id = $id;
        $this->name = $name;
        $this->last_color_harmony_importance = $last_color_harmony_importance;
        $this->last_season_harmony_importance = $last_season_harmony_importance;
        $this->secretToken = $secretToken;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getLastColorHarmonyImportance(): float {
        return $this->last_color_harmony_importance;
    }

    public function getLastSeasonHarmonyImportance(): float {
        return $this->last_season_harmony_importance;
    }

    public function getSecretToken(): string {
        return $this->secretToken;
    }
}
