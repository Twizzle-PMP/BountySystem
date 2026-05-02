<?php

declare(strict_types=1);

namespace Twizzle\BountySystem\data;

class BountyData {

    private string $targetName;
    private string $placedBy;
    private float $amount;
    private int $placedAt;

    public function __construct(string $targetName, string $placedBy, float $amount, int $placedAt) {
        $this->targetName = $targetName;
        $this->placedBy = $placedBy;
        $this->amount = $amount;
        $this->placedAt = $placedAt;
    }

    public function getTargetName(): string {
        return $this->targetName;
    }

    public function getPlacedBy(): string {
        return $this->placedBy;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    public function setAmount(float $amount): void {
        $this->amount = $amount;
    }

    public function getPlacedAt(): int {
        return $this->placedAt;
    }

    public function toArray(): array {
        return [
            "target" => $this->targetName,
            "placedBy" => $this->placedBy,
            "amount" => $this->amount,
            "placedAt" => $this->placedAt
        ];
    }

    public static function fromArray(array $data): self {
        return new self(
            $data["target"],
            $data["placedBy"],
            (float) $data["amount"],
            (int) $data["placedAt"]
        );
    }
}
