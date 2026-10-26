<?php

/**
 * User class representing a user.
 */

class User {
    private int $id;
    private string $name;
    private float $last_color_harmony_importance;
    private float $last_season_harmony_importance;

    public function __construct(int $id, string $name, float $last_color_harmony_importance, float $last_season_harmony_importance) {
        $this->id = $id;
        $this->name = $name;
        $this->last_color_harmony_importance = $last_color_harmony_importance;
        $this->last_season_harmony_importance = $last_season_harmony_importance;
    }

    public function getName(): string {
        return $this->name;
    }
}
