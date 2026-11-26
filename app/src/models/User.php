<?php

/**
 * User class representing a user.
 */

class User {
    private ?int $id;
    private string $name;
    private string $secretToken;

    public function __construct(string $name, ?int $id = null, string $secretToken = '') {
        $this->id = $id;
        $this->name = $name;
        $this->secretToken = $secretToken;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getSecretToken(): string {
        return $this->secretToken;
    }
}
