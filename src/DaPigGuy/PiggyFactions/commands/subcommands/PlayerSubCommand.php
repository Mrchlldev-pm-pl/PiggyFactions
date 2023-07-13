<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyFactions\commands\subcommands;

use CortexPE\Commando\args\TextArgument;
use DaPigGuy\PiggyFactions\PiggyFactions;
use DaPigGuy\PiggyFactions\utils\FormattedTime;
use DaPigGuy\PiggyFactions\utils\RoundValue;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class PlayerSubCommand extends FactionSubCommand
{
    protected bool $requiresPlayer = false;

    public function onBasicRun(CommandSender $sender, array $args): void
    {
        $player = $sender instanceof Player ? PiggyFactions::getInstance()->getPlayerManager()->getPlayer($sender) : null;
        if (isset($args["player"])) {
            $player = PiggyFactions::getInstance()->getPlayerManager()->getPlayerByName($args["player"]);
            if ($player === null) {
                PiggyFactions::getInstance()->getLanguageManager()->sendMessage($sender, "commands.invalid-player", ["{PLAYER}" => $args["player"]]);
                return;
            }
        }
        if ($player === null) {
            $this->sendUsage();
            return;
        }

        $config = PiggyFactions::getInstance()->getConfig();
        $time = RoundValue::round(($player->getMaxPower() - $player->getPower()) / $config->getNested("factions.power.per.hour"));
        $firstPlayed = (int)(PiggyFactions::getInstance()->getServer()->getOfflinePlayer($player->getUsername())->getFirstPlayed() / 1000);
        $faction = $player->getFaction();
        PiggyFactions::getInstance()->getLanguageManager()->sendMessage($sender, "commands.player.message", [
            "{PLAYER}" => $player->getUsername(),
            "{FACTION}" => $faction === null ? "None" : $faction->getName(),
            "{RANKSYMBOL}" => PiggyFactions::getInstance()->getTagManager()->getPlayerRankSymbol($player),
            "{POWER}" => RoundValue::round($player->getPower()),
            "{TOTALPOWER}" => $player->getMaxPower(),
            "{TIMETOMAXPOWER}" => $time <= 0 ? "" : PiggyFactions::getInstance()->getLanguageManager()->getMessage($sender instanceof Player ? PiggyFactions::getInstance()->getPlayerManager()->getPlayer($sender)->getLanguage() : PiggyFactions::getInstance()->getLanguageManager()->getDefaultLanguage(), "commands.player.time-to-max-power", ["{TIME}" => $time]),
            "{POWERPERHOUR}" => RoundValue::round($config->getNested("factions.power.per.hour")),
            "{POWERPERDEATH}" => RoundValue::round($config->getNested("factions.power.per.death")),
            "{CREATIONDATE}" => date("F j, Y @ g:i a T", $firstPlayed),
            "{AGE}" => FormattedTime::getLong($firstPlayed),
            "{SIMPLEAGE}" => FormattedTime::getShort($firstPlayed)
        ]);
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TextArgument("player", true));
    }
}