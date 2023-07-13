<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyFactions\commands;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\admin\AddPowerSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\admin\AdminSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\admin\powerboost\PowerBoostSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\admin\SetPowerSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\ChatSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\claims\claim\ClaimSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\claims\MapSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\claims\SeeChunkSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\claims\unclaim\UnclaimSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\DebugSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\flags\FlagSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\FlySubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\HelpSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\homes\HomeSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\homes\SetHomeSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\homes\UnsetHomeSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\InfoSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\JoinSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\LanguageSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\LeaveSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\management\BanSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\management\CreateSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\management\DescriptionSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\management\DisbandSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\management\InviteSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\management\KickSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\management\LogsSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\management\MotdSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\management\NameSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\management\UnbanSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\money\DepositSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\money\MoneySubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\money\WithdrawSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\PlayerSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\relations\AllySubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\relations\EnemySubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\relations\NeutralSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\relations\TruceSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\relations\UnallySubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\roles\DemoteSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\roles\LeaderSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\roles\PermissionSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\roles\PromoteSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\TopSubCommand;
use DaPigGuy\PiggyFactions\commands\subcommands\VersionSubCommand;
use DaPigGuy\PiggyFactions\PiggyFactions;
use DaPigGuy\PiggyFactions\utils\ChatTypes;
use Vecnavium\FormsUI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class FactionCommand extends BaseCommand
{
    /** @var PiggyFactions */
    protected $plugin;

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (PiggyFactions::getInstance()->areFormsEnabled() && $sender instanceof Player) {
            $subcommands = array_filter($this->getSubCommands(), function (BaseSubCommand $subCommand, string $alias) use ($sender): bool {
                return $subCommand->getName() === $alias && count(array_filter($subCommand->getPermissions(), $sender->hasPermission(...))) > 0;
            }, ARRAY_FILTER_USE_BOTH);
            $form = new SimpleForm(function (Player $player, ?int $data) use ($subcommands): void {
                if ($data !== null) {
                    $subcommand = $subcommands[array_keys($subcommands)[$data]];
                    $subcommand->onRun($player, $subcommand->getName(), []);
                }
            });
            $form->setTitle(PiggyFactions::getInstance()->getLanguageManager()->getMessage(PiggyFactions::getInstance()->getLanguageManager()->getPlayerLanguage($sender), "forms.title"));
            foreach ($subcommands as $key => $subcommand) {
                $form->addButton(ucfirst($subcommand->getName()));
            }
            $sender->sendForm($form);
            return;
        }
        $this->sendUsage();
    }

    protected function prepare(): void
    {
        $this->setPermission("piggyfactions.command.faction.use");

        $commands = [
            new AddPowerSubCommand( "addpower", "Add player power"),
            new AdminSubCommand( "admin", "Toggle admin mode"),
            new ChatSubCommand( ChatTypes::ALLY, "allychat", "Toggle ally chat", ["ac"]),
            new AllySubCommand( "ally", "Ally with other factions"),
            new BanSubCommand( "ban", "Ban a member from your faction"),
            new ChatSubCommand( ChatTypes::FACTION, "chat", "Toggle faction chat", ["c"]),
            new ClaimSubCommand( "claim", "Claim a chunk"),
            new CreateSubCommand( "create", "Create a faction"),
            new DebugSubCommand( "debug", "Dumps information for debugging"),
            new DemoteSubCommand( "demote", "Demote a faction member"),
            new DescriptionSubCommand( "description", "Set faction description", ["desc"]),
            new DisbandSubCommand( "disband", "Disband your faction"),
            new EnemySubCommand( "enemy", "Mark faction as an enemy"),
            new FlagSubCommand( "flag", "Manage faction flags"),
            new FlySubCommand( "fly", "Fly within faction territories"),
            new HelpSubCommand( $this, "help", "Display command information"),
            new HomeSubCommand( "home", "Teleport to faction home"),
            new InfoSubCommand( "info", "Display faction info", ["who"]),
            new InviteSubCommand( "invite", "Invite a player to your faction"),
            new JoinSubCommand( "join", "Join a faction"),
            new KickSubCommand( "kick", "Kick a member from your faction"),
            new LanguageSubCommand( "language", "Change personal language for PiggyFactions", ["lang"]),
            new LeaderSubCommand( "leader", "Transfer leadership of your faction"),
            new LeaveSubCommand( "leave", "Leave your faction"),
            new LogsSubCommand( "logs", "View your Factions logs", ["log"]),
            new MapSubCommand( "map", "View map of area"),
            new MotdSubCommand( "motd", "Set faction MOTD"),
            new NameSubCommand( "name", "Rename your faction"),
            new NeutralSubCommand( "neutral", "Reset relation with another faction"),
            new PermissionSubCommand( "permission", "Set faction role permissions", ["perms"]),
            new PlayerSubCommand( "player", "Display player info", ["p"]),
            new PowerBoostSubCommand( "powerboost", "Increases max power"),
            new PromoteSubCommand( "promote", "Promote a faction member"),
            new SeeChunkSubCommand( "seechunk", "Toggle chunk visualizer", ["sc"]),
            new SetHomeSubCommand( "sethome", "Set faction home"),
            new SetPowerSubCommand( "setpower", "Set player power"),
            new TopSubCommand( "top", "Display top factions", ["list"]),
            new TruceSubCommand( "truce", "Truce with other factions"),
            new UnallySubCommand( "unally", "End faction alliance"),
            new UnbanSubCommand( "unban", "Unban a member from your faction"),
            new UnclaimSubCommand( "unclaim", "Unclaim a chunk"),
            new UnsetHomeSubCommand( "unsethome", "Unset faction home", ["delhome"]),
            new VersionSubCommand( "version", "Display version & credits for PiggyFactions", ["v", "ver"]),
        ];

        $bank_commands = [
            new DepositSubCommand( "deposit", "Deposit money into faction bank"),
            new MoneySubCommand( "money", "View faction bank balance"),
            new WithdrawSubCommand( "withdraw", "Withdraw money from faction bank")
        ];

        foreach ($commands as $command) {
            if (!in_array($command->getName(), PiggyFactions::getInstance()->getConfig()->getNested("commands.disabled", []))){
                $this->registerSubCommand($command);
            }
        }

        if (PiggyFactions::getInstance()->isFactionBankEnabled()) {
            foreach ($bank_commands as $command) {
                $this->registerSubCommand($command);
            }
        }
    }
}