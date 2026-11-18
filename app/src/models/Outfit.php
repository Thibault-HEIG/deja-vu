<?php
/**
 * Outift class representing an outfit.
 */

class Outfit {
    private Item $top;
    private Item $bottom;
    private Item $shoes;
    private ?Item $accessory;
    private ?Item $jacket;

    public function __construct(Item $top, Item $bottom, Item $shoes, ?Item $accessory = null, ?Item $jacket = null) {
        $this->top = $top;
        $this->bottom = $bottom;
        $this->shoes = $shoes;
        $this->accessory = $accessory;
        $this->jacket = $jacket;
    }
}
