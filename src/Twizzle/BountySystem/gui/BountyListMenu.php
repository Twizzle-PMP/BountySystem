<?php

declare(strict_types=1);

namespace Twizzle\BountySystem\gui;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Twizzle\BountySystem\Loader;

class BountyListMenu {

    public static function open(Player $player, Loader $plugin): void {
        $bounties = array_values($plugin->getBountyManager()->getAllBounties());
        $symbol = $plugin->getConfig()->get("settings")["currency-symbol"];

        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->setName(TF::GOLD . "Active Bounties");

        $inv = $menu->getInventory();

        if (empty($bounties)) {
            $barrier = VanillaItems::PAPER()->setCustomName(TF::RED . "No active bounties!");
            $inv->setItem(22, $barrier);
        } else {
            $slot = 0;
            foreach ($bounties as $bounty) {
                if ($slot >= 54) break;
                $skull = VanillaBlocks::MOB_HEAD()->asItem();
                $skull->setCustomName(
                    TF::YELLOW . $bounty->getTargetName() . TF::EOL .
                    TF::GRAY . "Reward: " . TF::GOLD . $symbol . number_format($bounty->getAmount(), 2) . TF::EOL .
                    TF::GRAY . "Placed by: " . TF::WHITE . $bounty->getPlacedBy() . TF::EOL .
                    TF::GRAY . "Kill this player to collect!"
                );
                $skull->getNamedTag()->setString("bounty_target", $bounty->getTargetName());
                $inv->setItem($slot, $skull);
                $slot++;
            }
        }

        $menu->setListener(function(InvMenuTransaction $transaction): InvMenuTransactionResult {
            return $transaction->discard();
        });

        $menu->send($player);
    }
}