<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyFactions\commands\subcommands\claims\unclaim;

use DaPigGuy\PiggyFactions\commands\subcommands\FactionSubCommand;
use DaPigGuy\PiggyFactions\event\claims\UnclaimChunkEvent;
use DaPigGuy\PiggyFactions\factions\Faction;
use DaPigGuy\PiggyFactions\PiggyFactions;
use DaPigGuy\PiggyFactions\players\FactionsPlayer;
use pocketmine\player\Player;

abstract class UnclaimMultipleSubCommand extends FactionSubCommand
{
    protected ?string $parentNode = "unclaim";

    public function onNormalRun(Player $sender, ?Faction $faction, FactionsPlayer $member, string $aliasUsed, array $args): void
    {
        if (in_array($sender->getWorld()->getFolderName(), PiggyFactions::getInstance()->getConfig()->getNested("factions.claims.blacklisted-worlds"))) {
            $member->sendMessage("commands.unclaim.blacklisted-world");
            return;
        }
        $unclaimed = 0;
        $chunks = $this->getChunks($sender, $args);
        if (empty($chunks)) return;
        foreach ($chunks as $chunk) {
            $claim = PiggyFactions::getInstance()->getClaimsManager()->getClaim($chunk[0], $chunk[1], $sender->getWorld()->getFolderName());
            if ($claim !== null) {
                if ($claim->getFaction() === $faction || $member->isInAdminMode()) {
                    $ev = new UnclaimChunkEvent($faction, $member, $claim);
                    $ev->call();
                    if ($ev->isCancelled()) continue;

                    PiggyFactions::getInstance()->getClaimsManager()->deleteClaim($claim);
                    $unclaimed++;
                }
            }
        }
        $member->sendMessage("commands.unclaim.claimed-multiple", ["{AMOUNT}" => $unclaimed, "{COMMAND}" => $this->getName()]);
    }

    abstract public function getChunks(Player $player, array $args): array;
}