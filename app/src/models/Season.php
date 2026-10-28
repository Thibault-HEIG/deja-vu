<?php

/*Season : représente une saison (printemps, été, automne, hiver).*/
class Season {
    private int $id;
    private string $name;
    private int $avgTemperature;
    private ?string $iconUrl;

    public function __construct(int $id, string $name, int $avgTemperature, ?string $iconUrl) {
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
