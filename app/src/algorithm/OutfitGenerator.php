<?php
require_once __DIR__ . '/../constants.php';
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../models/Outfit.php';
require_once __DIR__ . '/../repositories/ItemRepository.php';

/**
 * OutfitGenerator — picks one item per category to build a coherent outfit.
 *
 * Algorithm overview:
 * 1. If a seed item is provided, it's locked into its category slot.
 * 2. For each remaining empty slot, score every candidate item against the
 *    items already chosen (the "context") using a weighted sum:
 *      totalScore = colorScore × colorWeight + seasonScore × seasonWeight + rateBonus
 * 3. Candidates are sorted by score, then one is picked using a temperature-
 *    weighted random selection (low temp → almost always the best, high → more random).
 * 4. For optional categories (jacket, accessory), a fake "no item" candidate
 *    competes: if it wins, the slot stays empty.
 */

class OutfitGenerator {
    private float $colorHarmonyWeight;
    private float $seasonHarmonyWeight;
    private ?Item $seedItem;
    private ?int $seasonWish;
    private float $modelTemperature;
    private int $userId;

    public function __construct(
        int $userId,
        float $colorHarmonyWeight = 0.5,
        float $seasonHarmonyWeight = 0.5,
        ?Item $seedItem = null,
        ?int $seasonWish = null,
        float $modelTemperature = ALGORITHM_TEMPERATURE
    ) {
        if ($colorHarmonyWeight < 0 || $colorHarmonyWeight > 1) {
            throw new InvalidArgumentException("Color harmony weight must be between 0 and 1.");
        }
        if ($seasonHarmonyWeight < 0 || $seasonHarmonyWeight > 1) {
            throw new InvalidArgumentException("Season harmony weight must be between 0 and 1.");
        }
        if ($modelTemperature < 0 || $modelTemperature > 1) {
            throw new InvalidArgumentException("Model temperature must be between 0 and 1.");
        }
        if (abs(1 - ($colorHarmonyWeight + $seasonHarmonyWeight)) > 0.001) {
            throw new InvalidArgumentException("The sum of color and season harmony importance must be 1.");
        }
        if ($seasonWish !== null && ($seasonWish < -20 || $seasonWish > 50)) {
            throw new InvalidArgumentException("Season wish must be between -20 and 50.");
        }

        $this->userId = $userId;
        $this->colorHarmonyWeight = $colorHarmonyWeight;
        $this->seasonHarmonyWeight = $seasonHarmonyWeight;
        $this->seedItem = $seedItem;
        $this->seasonWish = $seasonWish;
        $this->modelTemperature = $modelTemperature;
    }

    public function generateOutfit(): Outfit {
        $slots = [
            'top'       => null,
            'bottom'    => null,
            'shoes'     => null,
            'accessory' => null,
            'jacket'    => null,
        ];

        // Lock the seed item into its slot
        if ($this->seedItem !== null) {
            $category = $this->seedItem->getCategory();
            $slots[$category] = $this->seedItem;
        }

        // Fill each empty slot
        foreach ($slots as $category => $currentItem) {
            if ($currentItem !== null) {
                continue; // already filled by seed
            }

            // Context = every item already chosen in the outfit so far
            $context = [];
            foreach ($slots as $slotItem) {
                if ($slotItem !== null) {
                    $context[] = $slotItem;
                }
            }

            // Fetch all items of this category owned by the user
            $itemRepo = new ItemRepository();
            $categoryItems = $itemRepo->getAllByUserIdAndCategory($this->userId, $category);

            // Score each candidate
            $candidates = [];
            foreach ($categoryItems as $item) {
                $colorScore  = $this->computeColorScoreVsContext($item, $context);
                $seasonScore = ($this->seasonWish !== null)
                    ? $this->computeSeasonScoreAbsolute($item->getSeason()->getAvgTemperature())
                    : $this->computeSeasonScoreVsContext($item, $context);

                $candidates[] = [
                    'item'   => $item,
                    'color'  => $colorScore,
                    'season' => $seasonScore,
                    'rate'   => $item->getRate() / 5, // normalize to 0-1
                ];
            }

            // For optional categories, add a "no item" candidate with mediocre scores.
            if (in_array($category, ['accessory', 'jacket'])) {
                $candidates[] = [
                    'item'   => null,
                    'color'  => 0.4,
                    'season' => 0.4,
                    'rate'   => 3 / 5,
                ];
            }

            // Compute total weighted score for each candidate
            for ($i = 0; $i < count($candidates); $i++) {
                $candidates[$i]['total'] = $candidates[$i]['color']  * $this->colorHarmonyWeight
                    + $candidates[$i]['season'] * $this->seasonHarmonyWeight
                    + $candidates[$i]['rate']   * RATE_IMPORTANCE;
            }

            // Sort descending by total score (best first).
            // usort needs a comparison function.
            usort($candidates, function ($a, $b) {
                return $b['total'] - $a['total'];
            });

            // Pick one item using temperature-weighted random selection
            $slots[$category] = $this->pickByTemperature($candidates);
        }

        if ($slots['top'] === null || $slots['bottom'] === null || $slots['shoes'] === null) {
            throw new RuntimeException("Impossible de générer une tenue : haut, bas et chaussures sont obligatoires.");
        }

        return new Outfit(
            $slots['top'],
            $slots['bottom'],
            $slots['shoes'],
            $slots['accessory'],
            $slots['jacket'],
        );
    }

    /**
     * Pick a candidate from the sorted list.
     *
     * Each candidate's score is raised to a power that depends on temperature:
     *   power = temperature / (1 - temperature)
     *
     * - temp 0   → power ∞ → best score dominates → always the best.
     * - temp 0.5 → power 1 → probability proportional to raw score.
     * - temp 1   → power 0 → all scores become 1 → fully random.
     */
    private function pickByTemperature(array $candidates): ?Item {
        // temp ≈ 0 → always the best (and avoids division by zero)
        if ($this->modelTemperature <= 0.01) {
            return $candidates[0]['item'];
        }

        // Higher power = bigger gap between good and bad scores
        $power = (1 - $this->modelTemperature) / $this->modelTemperature;

        // Build a weight for each candidate: score raised to that power
        $weights = [];
        $totalWeight = 0.0;
        for ($i = 0; $i < count($candidates); $i++) {
            $w = pow($candidates[$i]['total'], $power);
            $weights[] = $w;
            $totalWeight += $w;
        }

        // Weighted random draw: pick a random point on a number line
        // of length totalWeight, then walk through segments until we land
        $roll = (mt_rand() / mt_getrandmax()) * $totalWeight;
        $cumulative = 0.0;
        for ($i = 0; $i < count($candidates); $i++) {
            $cumulative += $weights[$i];
            if ($roll <= $cumulative) {
                return $candidates[$i]['item'];
            }
        }

        return $candidates[0]['item'];
    }

    /**
     * Average color harmony score of $item against every item in the context.
     * Returns a neutral 0.5 when context is empty (first item, no seed).
     */
    private function computeColorScoreVsContext(Item $item, array $context): float {
        if (empty($context)) {
            return 0.5;
        }

        $sum = 0.0;
        foreach ($context as $contextItem) {
            $sum += $this->computeColorScore($item->getColor(), $contextItem->getColor());
        }
        return $sum / count($context);
    }

    /**
     * Average season harmony score of $item against every item in the context.
     * Returns a neutral 0.5 when context is empty.
     */
    private function computeSeasonScoreVsContext(Item $item, array $context): float {
        if (empty($context)) {
            return 0.5;
        }

        $sum = 0.0;
        foreach ($context as $contextItem) {
            $sum += $this->computeSeasonScoreRelative(
                $item->getSeason()->getAvgTemperature(),
                $contextItem->getSeason()->getAvgTemperature()
            );
        }
        return $sum / count($context);
    }

    /**
     * How well does this item's season match the user's wished temperature?
     * Higher = better match.
     */
    private function computeSeasonScoreAbsolute(int $seasonTemp): float {
        // TODO: implement
        return 0.0;
    }

    /**
     * How well do two items' seasons match each other?
     * Higher = better match.
     */
    private function computeSeasonScoreRelative(int $seasonTemp1, int $seasonTemp2): float {
        // TODO: implement
        return 0.0;
    }

    /**
     * How well do two colors harmonize (complementary, analogous, etc.)?
     * Higher = better match.
     */
    private function computeColorScore(Color $color1, Color $color2): float {
        // TODO: implement
        return 0.0;
    }
}
