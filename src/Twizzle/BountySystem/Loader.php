<?php

declare(strict_types=1);

namespace Twizzle\BountySystem;

use onebone\economyapi\EconomyAPI;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use Twizzle\BountySystem\command\BountyCommand;
use Twizzle\BountySystem\manager\BountyManager;

class Loader extends PluginBase implements Listener {

    private BountyManager $bountyManager;

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $this->saveResource("config.yml");

        $this->bountyManager = new BountyManager($this);
        $this->getServer()->getCommandMap()->register("bountysystem", new BountyCommand($this));
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->getScheduler()->scheduleRepeatingTask(new \pocketmine\scheduler\ClosureTask(function(): void {
            $this->bountyManager->purgeExpired();
        }), 20 * 60 * 10);

        $this->getLogger()->info(TF::GREEN . "BountySystem enabled by Twizzle.");
    }

    protected function onDisable(): void {
        $this->bountyManager->saveData();
        $this->getLogger()->info(TF::RED . "BountySystem disabled.");
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();

        if (!$cause instanceof EntityDamageByEntityEvent) {
            return;
        }

        $killer = $cause->getDamager();

        if (!$killer instanceof Player) {
            return;
        }

        $manager = $this->bountyManager;

        if (!$manager->hasBounty($player->getName())) {
            return;
        }

        $bounty = $manager->collectBounty($killer->getName(), $player->getName());

        if ($bounty === null) {
            return;
        }

        $cfg = $this->getConfig()->get("settings");
        $msgs = $this->getConfig()->get("messages");
        $symbol = $cfg["currency-symbol"];
        $prefix = TF::colorize($msgs["prefix"]);

        EconomyAPI::getInstance()->addMoney($killer, $bounty->getAmount());

        $killerMsg = str_replace(
            ["{target}", "{amount}"],
            [$player->getName(), $symbol . number_format($bounty->getAmount(), 2)],
            $msgs["bounty-collected"]
        );
        $killer->sendMessage($prefix . TF::colorize($killerMsg));

        $broadcast = TF::GOLD . "[Bounty] " . TF::YELLOW . $killer->getName() . TF::GRAY . " collected the bounty on " . TF::RED . $player->getName() . TF::GRAY . " for " . TF::GOLD . $symbol . number_format($bounty->getAmount(), 2) . "!";
        $this->getServer()->broadcastMessage($broadcast);
    }

    public function getBountyManager(): BountyManager {
        return $this->bountyManager;
    }
}
