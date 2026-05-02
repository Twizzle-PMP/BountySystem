<?php

declare(strict_types=1);

namespace Twizzle\BountySystem\manager;

use Twizzle\BountySystem\data\BountyData;
use Twizzle\BountySystem\Loader;

class BountyManager {

    private Loader $plugin;
    private array $activeBounties = [];
    private array $history = [];
    private string $dataFolder;

    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
        $this->dataFolder = $plugin->getDataFolder() . "data/";
        @mkdir($this->dataFolder);
        $this->loadData();
    }

    public function loadData(): void {
        $activePath = $this->dataFolder . "bounties.json";
        $historyPath = $this->dataFolder . "history.json";

        if (file_exists($activePath)) {
            $raw = json_decode(file_get_contents($activePath), true) ?? [];
            foreach ($raw as $target => $data) {
                $this->activeBounties[strtolower($target)] = BountyData::fromArray($data);
            }
        }

        if (file_exists($historyPath)) {
            $this->history = json_decode(file_get_contents($historyPath), true) ?? [];
        }
    }

    public function saveData(): void {
        $rawBounties = [];
        foreach ($this->activeBounties as $key => $bounty) {
            $rawBounties[$key] = $bounty->toArray();
        }

        file_put_contents($this->dataFolder . "bounties.json", json_encode($rawBounties, JSON_PRETTY_PRINT));
        file_put_contents($this->dataFolder . "history.json", json_encode($this->history, JSON_PRETTY_PRINT));
    }

    public function placeBounty(string $placedBy, string $target, float $amount): void {
        $key = strtolower($target);
        if (isset($this->activeBounties[$key])) {
            $this->activeBounties[$key]->setAmount($this->activeBounties[$key]->getAmount() + $amount);
        } else {
            $this->activeBounties[$key] = new BountyData($target, $placedBy, $amount, time());
        }
        $this->saveData();
    }

    public function cancelBounty(string $target): ?BountyData {
        $key = strtolower($target);
        if (!isset($this->activeBounties[$key])) {
            return null;
        }
        $bounty = $this->activeBounties[$key];
        unset($this->activeBounties[$key]);
        $this->saveData();
        return $bounty;
    }

    public function collectBounty(string $killer, string $target): ?BountyData {
        $key = strtolower($target);
        if (!isset($this->activeBounties[$key])) {
            return null;
        }
        $bounty = $this->activeBounties[$key];
        unset($this->activeBounties[$key]);

        $historyEntry = [
            "target" => $bounty->getTargetName(),
            "placedBy" => $bounty->getPlacedBy(),
            "collectedBy" => $killer,
            "amount" => $bounty->getAmount(),
            "collectedAt" => time()
        ];

        array_unshift($this->history, $historyEntry);
        $limit = (int) $this->plugin->getConfig()->get("settings")["history-limit"];
        if (count($this->history) > $limit) {
            $this->history = array_slice($this->history, 0, $limit);
        }

        $this->saveData();
        return $bounty;
    }

    public function hasBounty(string $target): bool {
        return isset($this->activeBounties[strtolower($target)]);
    }

    public function getBounty(string $target): ?BountyData {
        return $this->activeBounties[strtolower($target)] ?? null;
    }

    public function getAllBounties(): array {
        return $this->activeBounties;
    }

    public function getHistory(): array {
        return $this->history;
    }

    public function getExpiredBounties(): array {
        $expireDays = (int) $this->plugin->getConfig()->get("settings")["bounty-expire-days"];
        $expireSeconds = $expireDays * 86400;
        $expired = [];
        foreach ($this->activeBounties as $key => $bounty) {
            if ((time() - $bounty->getPlacedAt()) >= $expireSeconds) {
                $expired[$key] = $bounty;
            }
        }
        return $expired;
    }

    public function purgeExpired(): void {
        $expired = $this->getExpiredBounties();
        foreach (array_keys($expired) as $key) {
            unset($this->activeBounties[$key]);
        }
        if (!empty($expired)) {
            $this->saveData();
        }
    }
}
