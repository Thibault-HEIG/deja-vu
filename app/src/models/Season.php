<?php

/*Season : représente une saison (printemps, été, automne, hiver).*/
class Season {
    private int $id;
    private ?string $name;
    private ?int $avgTemperature;
    private ?string $iconUrl;

    public function __construct(int $id, ?string $name = null, ?int $avgTemperature = null, ?string $iconUrl = null) {
        if ($id < 1 || $id > 4) {
            throw new InvalidArgumentException("Season ID should be between 1 and 4.");
        }
        if ($avgTemperature !== null && ($avgTemperature < -20 || $avgTemperature > 50)) {
            throw new InvalidArgumentException("Average temperature mustn't exeed -20 and 50.");
        }
        $this->id = $id;
        $this->name = $name;
        $this->avgTemperature = $avgTemperature;
        $this->iconUrl = $iconUrl;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getAvgTemperature(): int {
        return $this->avgTemperature;
    }

    public function getIconUrl(): ?string {
        return $this->iconUrl;
    }
}
