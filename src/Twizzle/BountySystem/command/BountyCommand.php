<?php

declare(strict_types=1);

namespace Twizzle\BountySystem\command;

use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Twizzle\BountySystem\gui\BountyMainMenu;
use Twizzle\BountySystem\gui\BountyHistoryMenu;
use Twizzle\BountySystem\gui\BountyListMenu;
use Twizzle\BountySystem\Loader;

class BountyCommand extends Command {

    private Loader $plugin;

    public function __construct(Loader $plugin) {
        parent::__construct("bounty", "Bounty system command", "/bounty [place|cancel|list|history|top]", ["b"]);
        $this->setPermission("bountysystem.use");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "Use this command in-game.");
            return false;
        }

        if (!$this->testPermission($sender)) {
            return false;
        }

        $cfg = $this->plugin->getConfig()->get("settings");
        $msgs = $this->plugin->getConfig()->get("messages");
        $prefix = TF::colorize($msgs["prefix"]);
        $symbol = $cfg["currency-symbol"];

        if (empty($args)) {
            BountyMainMenu::open($sender, $this->plugin);
            return true;
        }

        switch (strtolower($args[0])) {
            case "place":
            case "set":
                if (!isset($args[1], $args[2])) {
                    $sender->sendMessage($prefix . TF::RED . "Usage: /bounty place <player> <amount>");
                    return false;
                }

                $targetName = $args[1];
                $amount = (float) $args[2];

                if (!is_numeric($args[2]) || $amount <= 0) {
                    $sender->sendMessage($prefix . TF::RED . "Invalid amount.");
                    return false;
                }

                if ($amount < (float) $cfg["min-bounty"]) {
                    $sender->sendMessage($prefix . TF::colorize(str_replace("{min}", $symbol . $cfg["min-bounty"], $msgs["bounty-too-low"])));
                    return false;
                }

                if ($amount > (float) $cfg["max-bounty"]) {
                    $sender->sendMessage($prefix . TF::colorize(str_replace("{max}", $symbol . $cfg["max-bounty"], $msgs["bounty-too-high"])));
                    return false;
                }

                if (!$cfg["allow-self-bounty"] && strtolower($targetName) === strtolower($sender->getName())) {
                    $sender->sendMessage($prefix . TF::colorize($msgs["self-bounty"]));
                    return false;
                }

                $economy = EconomyAPI::getInstance();
                if ($economy->myMoney($sender) < $amount) {
                    $sender->sendMessage($prefix . TF::colorize(str_replace("{amount}", $symbol . number_format($amount, 2), $msgs["not-enough-money"])));
                    return false;
                }

                $economy->reduceMoney($sender, $amount);
                $this->plugin->getBountyManager()->placeBounty($sender->getName(), $targetName, $amount);

                $msg = str_replace(["{amount}", "{target}"], [$symbol . number_format($amount, 2), $targetName], $msgs["bounty-set"]);
                $sender->sendMessage($prefix . TF::colorize($msg));

                $target = $this->plugin->getServer()->getPlayerExact($targetName);
                if ($target !== null) {
                    $target->sendMessage($prefix . TF::RED . "A bounty of " . TF::GOLD . $symbol . number_format($amount, 2) . TF::RED . " has been placed on your head!");
                }
                break;

            case "cancel":
                if (!isset($args[1])) {
                    $sender->sendMessage($prefix . TF::RED . "Usage: /bounty cancel <player>");
                    return false;
                }

                $targetName = $args[1];
                $manager = $this->plugin->getBountyManager();
                $bounty = $manager->getBounty($targetName);

                if ($bounty === null) {
                    $sender->sendMessage($prefix . TF::colorize($msgs["no-bounty"]));
                    return false;
                }

                $isAdmin = $sender->hasPermission("bountysystem.admin");
                if (!$isAdmin && strtolower($bounty->getPlacedBy()) !== strtolower($sender->getName())) {
                    $sender->sendMessage($prefix . TF::RED . "You can only cancel bounties you placed.");
                    return false;
                }

                $removed = $manager->cancelBounty($targetName);
                if ($removed !== null) {
                    EconomyAPI::getInstance()->addMoney($sender, $removed->getAmount());
                    $msg = str_replace(
                        ["{target}", "{amount}"],
                        [$targetName, $symbol . number_format($removed->getAmount(), 2)],
                        $msgs["bounty-cancelled"]
                    );
                    $sender->sendMessage($prefix . TF::colorize($msg));
                }
                break;

            case "list":
                BountyListMenu::open($sender, $this->plugin);
                break;

            case "history":
                BountyHistoryMenu::open($sender, $this->plugin);
                break;

            case "top":
                $bounties = $this->plugin->getBountyManager()->getAllBounties();
                if (empty($bounties)) {
                    $sender->sendMessage($prefix . TF::RED . "No active bounties.");
                    return true;
                }
                usort($bounties, fn($a, $b) => $b->getAmount() <=> $a->getAmount());
                $sender->sendMessage(TF::GOLD . "--- Top Bounties ---");
                $i = 1;
                foreach (array_slice($bounties, 0, 10) as $b) {
                    $sender->sendMessage(TF::YELLOW . "#$i " . TF::WHITE . $b->getTargetName() . TF::GRAY . " - " . TF::GOLD . $symbol . number_format($b->getAmount(), 2));
                    $i++;
                }
                break;

            default:
                $sender->sendMessage($prefix . TF::RED . "Usage: /bounty [place|cancel|list|history|top]");
                break;
        }

        return true;
    }
}
