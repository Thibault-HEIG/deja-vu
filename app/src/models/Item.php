<?php
/**
 * Item class representing an item.
 */

class Item {
    private int $id;
    private string $category;
    private float $hue;
    private float $saturation;
    private float $brightness;
    private int $season_id;
    private int $user_rate;
    private string $img_url;

    public function __construct(int $id, string $category, float $hue, float $saturation, float $brightness, int $season_id, int $user_rate, string $img_url) {
        $this->id = $id;
        $this->category = $category;
        $this->hue = $hue;
        $this->saturation = $saturation;
        $this->brightness = $brightness;
        $this->season_id = $season_id;
        $this->user_rate = $user_rate;
        $this->img_url = $img_url;
    }

    public function getImgUrl(): string {
        return $this->img_url;
    }

    public function getCategory(): string {
        return $this->category;
    }

    public function getRate(): int {
        return $this->user_rate;
    }
}
