<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyFactions\commands\subcommands\claims\unclaim;

use DaPigGuy\PiggyFactions\commands\subcommands\FactionSubCommand;
use DaPigGuy\PiggyFactions\event\claims\UnclaimChunkEvent;
use DaPigGuy\PiggyFactions\factions\Faction;
use DaPigGuy\PiggyFactions\PiggyFactions;
use DaPigGuy\PiggyFactions\players\FactionsPlayer;
use pocketmine\player\Player;

class UnclaimSubCommand extends FactionSubCommand
{
    public function onNormalRun(Player $sender, ?Faction $faction, FactionsPlayer $member, string $aliasUsed, array $args): void
    {
        $claim = PiggyFactions::getInstance()->getClaimsManager()->getClaimByPosition($sender->getPosition());
        if ($claim === null) {
            $member->sendMessage("commands.unclaim.not-claimed");
            return;
        }
        if ($claim->getFaction() !== $faction && !$member->isInAdminMode()) {
            $member->sendMessage("commands.unclaim.other-faction");
            return;
        }

        $ev = new UnclaimChunkEvent($faction, $member, $claim);
        $ev->call();
        if ($ev->isCancelled()) return;

        PiggyFactions::getInstance()->getClaimsManager()->deleteClaim($claim);
        $member->sendMessage("commands.unclaim.success");
    }

    protected function prepare(): void
    {
        if (PiggyFactions::getInstance()->getConfig()->getNested("factions.claims.unclaimall", true)) {
            $this->registerSubCommand(new UnclaimAllSubCommand("all", "Unclaims all claims"));
        }
        if (PiggyFactions::getInstance()->getConfig()->getNested("factions.claims.autoclaim", true)) {
            $this->registerSubCommand(new UnclaimAutoSubCommand("auto", "Automatically unclaim chunks", ["a"]));
        }
        if (PiggyFactions::getInstance()->getConfig()->getNested("factions.claims.circle.enabled", true)) {
            $this->registerSubCommand(new UnclaimCircleSubCommand("circle", "Unclaims chunks in a circle radius", ["c"]));
        }
        if (PiggyFactions::getInstance()->getConfig()->getNested("factions.claims.square.enabled", true)) {
            $this->registerSubCommand(new UnclaimSquareSubCommand("square", "Unclaims chunks in a square radius", ["s"]));
        }
    }
}