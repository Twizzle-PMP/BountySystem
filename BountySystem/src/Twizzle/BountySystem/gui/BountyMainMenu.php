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

class BountyMainMenu {

    public static function open(Player $player, Loader $plugin): void {
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $menu->setName(TF::GOLD . "Bounty System");

        $inv = $menu->getInventory();

        $skull = VanillaBlocks::MOB_HEAD()->asItem()
            ->setCustomName(TF::YELLOW . "Place Bounty" . TF::EOL . TF::GRAY . "Put a bounty on a player");
        $skull->getNamedTag()->setString("action", "place");
        $inv->setItem(11, $skull);

        $paper = VanillaItems::PAPER()
            ->setCustomName(TF::AQUA . "Active Bounties" . TF::EOL . TF::GRAY . "View all active bounties");
        $paper->getNamedTag()->setString("action", "list");
        $inv->setItem(13, $paper);

        $book = VanillaItems::BOOK()
            ->setCustomName(TF::GREEN . "Bounty History" . TF::EOL . TF::GRAY . "View collected bounties");
        $book->getNamedTag()->setString("action", "history");
        $inv->setItem(15, $book);

        $menu->setListener(function(InvMenuTransaction $transaction) use ($plugin): InvMenuTransactionResult {
            $item = $transaction->getItemClicked();
            $tag = $item->getNamedTag();

            if ($tag->getTag("action") === null) {
                return $transaction->discard();
            }

            $action = $tag->getString("action");
            $player = $transaction->getPlayer();

            $player->removeCurrentWindow();

            switch ($action) {
                case "place":
                    $player->sendMessage(TF::YELLOW . "Type the player name to place a bounty on in chat, or use /bounty place <player> <amount>");
                    break;

                case "list":
                    BountyListMenu::open($player, $plugin);
                    break;

                case "history":
                    BountyHistoryMenu::open($player, $plugin);
                    break;
            }

            return $transaction->discard();
        });

        $menu->send($player);
    }
}