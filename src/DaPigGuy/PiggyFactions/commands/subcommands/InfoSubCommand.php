<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyFactions\commands\subcommands;

use CortexPE\Commando\args\TextArgument;
use DaPigGuy\PiggyFactions\factions\Faction;
use DaPigGuy\PiggyFactions\PiggyFactions;
use DaPigGuy\PiggyFactions\utils\FormattedTime;
use DaPigGuy\PiggyFactions\utils\Roles;
use DaPigGuy\PiggyFactions\utils\RoundValue;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class InfoSubCommand extends FactionSubCommand
{
    protected bool $requiresPlayer = false;

    public function onBasicRun(CommandSender $sender, array $args): void
    {
        $faction = $sender instanceof Player ? PiggyFactions::getInstance()->getPlayerManager()->getPlayerFaction($sender->getUniqueId()) : null;
        if (isset($args["faction"])) {
            $faction = PiggyFactions::getInstance()->getFactionsManager()->getFactionByName($args["faction"]);
            if ($faction === null) {
                PiggyFactions::getInstance()->getLanguageManager()->sendMessage($sender, "commands.invalid-faction", ["{FACTION}" => $args["faction"]]);
                return;
            }
        }
        if ($faction === null) {
            $this->sendUsage();
            return;
        }

        $memberNamesWithRole = [];
        $memberNamesByRole = [];
        foreach ($faction->getMembers() as $m) {
            $memberNamesByRole[$m->getRole()][] = $m->getUsername();
            $memberNamesWithRole[] = PiggyFactions::getInstance()->getTagManager()->getPlayerRankSymbol($m) . $m->getUsername();
        }

        PiggyFactions::getInstance()->getLanguageManager()->sendMessage($sender, "commands.info.message", [
            "{FACTION}" => $faction->getName(),
            "{DESCRIPTION}" => $faction->getDescription() ?? PiggyFactions::getInstance()->getLanguageManager()->getMessage($sender instanceof Player ? PiggyFactions::getInstance()->getPlayerManager()->getPlayer($sender)->getLanguage() : PiggyFactions::getInstance()->getLanguageManager()->getDefaultLanguage(), "commands.info.description-not-set"),
            "{CLAIMS}" => count(PiggyFactions::getInstance()->getClaimsManager()->getFactionClaims($faction)),
            "{POWER}" => RoundValue::round($faction->getPower()),
            "{TOTALPOWER}" => $faction->getMaxPower(),
            "{CREATIONDATE}" => date("F j, Y @ g:i a T", $faction->getCreationTime()),
            "{AGE}" => FormattedTime::getLong($faction->getCreationTime()),
            "{SIMPLEAGE}" => FormattedTime::getShort($faction->getCreationTime()),
            "{MONEY}" => RoundValue::round($faction->getMoney()),
            "{LEADER}" => ($leader = $faction->getLeader()) === null ? "" : $leader->getUsername(),
            "{ALLIES}" => implode(", ", array_map(function (Faction $f): string {
                return $f->getName();
            }, $faction->getAllies())),
            "{ENEMIES}" => implode(", ", array_map(function (Faction $f): string {
                return $f->getName();
            }, $faction->getEnemies())),
            "{RELATIONS}" => implode(", ", array_map(function (Faction $f) use ($faction): string {
                $color = ["enemy" => PiggyFactions::getInstance()->getLanguageManager()->translateColorTags(PiggyFactions::getInstance()->getConfig()->getNested("symbols.colors.relations.enemy")), "ally" => PiggyFactions::getInstance()->getLanguageManager()->translateColorTags(PiggyFactions::getInstance()->getConfig()->getNested("symbols.colors.relations.ally"))];
                return $color[$faction->getRelation($f)] . $f->getName();
            }, array_merge($faction->getAllies(), $faction->getEnemies()))),
            "{OFFICERS}" => implode(", ", $memberNamesByRole[Roles::OFFICER] ?? []),
            "{MEMBERS}" => implode(", ", $memberNamesByRole[Roles::MEMBER] ?? []),
            "{RECRUITS}" => implode(", ", $memberNamesByRole[Roles::RECRUIT] ?? []),
            "{PLAYERS}" => implode(", ", $memberNamesWithRole),
            "{TOTALPLAYERS}" => count($faction->getMembers()),
            "{ONLINECOUNT}" => count($faction->getOnlineMembers())
        ]);
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TextArgument("faction", true));
    }
}