<?php

/**
 * Outfit class representing an outfit and its generation metadata.
 */

class Outfit {
    private ?int $id;
    private Item $top;
    private Item $bottom;
    private Item $shoes;
    private ?Item $accessory;
    private ?Item $jacket;

    private ?float $seasonHarmony;
    private ?int $seedItemId;
    private ?int $seasonWishId;
    private ?string $timestamp;

    public function __construct(
        Item $top,
        Item $bottom,
        Item $shoes,
        ?Item $accessory = null,
        ?Item $jacket = null,
        ?float $seasonHarmony = null,
        ?int $seedItemId = null,
        ?int $seasonWishId = null,
        ?int $id = null,
        ?string $timestamp = null
    ) {
        $this->top = $top;
        $this->bottom = $bottom;
        $this->shoes = $shoes;
        $this->accessory = $accessory;
        $this->jacket = $jacket;

        $this->seasonHarmony = $seasonHarmony;
        $this->seedItemId = $seedItemId;
        $this->seasonWishId = $seasonWishId;
        $this->id = $id;
        $this->timestamp = $timestamp;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getTop(): Item {
        return $this->top;
    }

    public function getBottom(): Item {
        return $this->bottom;
    }

    public function getShoes(): Item {
        return $this->shoes;
    }

    public function getAccessory(): ?Item {
        return $this->accessory;
    }

    public function getJacket(): ?Item {
        return $this->jacket;
    }

    public function getSeasonHarmony(): ?float {
        return $this->seasonHarmony;
    }

    public function getColorHarmony(): ?float {
        return isset($this->seasonHarmony) ? 1.0 - $this->seasonHarmony : null;
    }

    public function getSeedItemId(): ?int {
        return $this->seedItemId;
    }

    public function getSeasonWishId(): ?int {
        return $this->seasonWishId;
    }

    public function getTimestamp(): ?string {
        return $this->timestamp;
    }
}
