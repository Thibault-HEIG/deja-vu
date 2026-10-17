<?php

/**
 * OutfitGenerator class for generating outfits based on items and their attributes.
 */

class OutfitGenerator {
    private float $colorHarmonyWeight;
    private float $seasonHarmonyWeight;
    private ?Item $seedItem;
    private int $seasonWish; // temperature wish in °C
    
    private float $modelTemperature; // Randomness factor for the model
}
