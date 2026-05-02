<?php

declare(strict_types=1);

namespace Twizzle\BountySystem\gui;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Twizzle\BountySystem\Loader;

class BountyHistoryMenu {

    public static function open(Player $player, Loader $plugin): void {
        $history = $plugin->getBountyManager()->getHistory();
        $symbol = $plugin->getConfig()->get("settings")["currency-symbol"];

        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->setName(TF::GOLD . "Bounty History");

        $inv = $menu->getInventory();

        if (empty($history)) {
            $book = VanillaItems::BOOK()->setCustomName(TF::RED . "No bounty history yet!");
            $inv->setItem(22, $book);
        } else {
            $slot = 0;
            foreach ($history as $entry) {
                if ($slot >= 54) break;
                $date = date("Y-m-d H:i", $entry["collectedAt"]);
                $book = VanillaItems::BOOK();
                $book->setCustomName(
                    TF::RED . $entry["target"] . TF::GRAY . " was killed" . TF::EOL .
                    TF::GRAY . "Collected by: " . TF::GREEN . $entry["collectedBy"] . TF::EOL .
                    TF::GRAY . "Reward: " . TF::GOLD . $symbol . number_format((float)$entry["amount"], 2) . TF::EOL .
                    TF::GRAY . "Placed by: " . TF::WHITE . $entry["placedBy"] . TF::EOL .
                    TF::GRAY . "Date: " . TF::WHITE . $date
                );
                $inv->setItem($slot, $book);
                $slot++;
            }
        }

        $menu->setListener(function(InvMenuTransaction $transaction): InvMenuTransactionResult {
            return $transaction->discard();
        });

        $menu->send($player);
    }
}
