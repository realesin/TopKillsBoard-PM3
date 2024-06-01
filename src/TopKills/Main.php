<?php

namespace TopKills;

use pocketmine\plugin\PluginBase;
use pocketmine\plugin\Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\Config;
use pocketmine\command\{
	Command, CommandSender
};
use pocketmine\utils\TextFormat;
use pocketmine\event\player\{PlayerInteractEvent, PlayerMoveEvent, PlayerRespawnEvent, PlayerDeathEvent, PlayerItemHeldEvent};
use pocketmine\utils\TextFormat as C;
use pocketmine\Player;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use pocketmine\event\entity\{EntityDamageEvent, EntityDamageByEntityEvent};

class Main extends PluginBase implements Listener{

	public function onEnable(){
		if(!($pl = $this->getServer()->getPluginManager()->getPlugin("DevTools")) instanceof Plugin){
		} else {
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
			if(!is_dir($this->getDataFolder())) @mkdir($this->getDataFolder());
			$this->saveResource("config.yml");
			$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
			$cfg->save();
			$this->getLogger()->info("TopKills LeaderBoard Enabled!");
		}
	}
	
	public function TopKills(PlayerDeathEvent $e){
		$player = $e->getPlayer();
		$causa = $e->getEntity()->getLastDamageCause();
		if($causa instanceof EntityDamageByEntityEvent){
			$attakr = $causa->getDamager();
			$cfg = new Config($this->getDataFolder() . "kills.yml", Config::YAML);
		    $cfg->set($player->getName(), $cfg->get($player->getName()) + 1);
		    $cfg->save();	
		}
	}

	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$killcfg = new Config($this->getDataFolder() . "kills.yml", Config::YAML, [
			$player->getName() => 0
		]);
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$kills = $killcfg->getAll();
		arsort($kills);
		$title = "§l§eLeaderboard" . "§r\n§b[SERVER] §7Kills§r" . "\n\n";
		$i = 0;
		foreach($kills as $playerName => $killCount){
			$i++;
			if($i < 11 && $killCount){
				switch($i){
					case 1:
						$place = C::GREEN . "#1";
						$y = $i / 4.125;
						break;
					case 2:
						$place = C::YELLOW . "#2";
						$y = $i / 4.125;
						break;
					case 3:
						$place = C::GOLD . "#3";
						$y = $i / 4.125;
						break;
					default:
						$place = C::RED . "#" . $i;
						$y = $i / 4.125;
						break;
				}
				$this->getServer()->getDefaultLevel()->addParticle(new FloatingTextParticle(new Vector3($config->get("LeaderBoards-X") + 0.5, $config->get("LeaderBoards-Y") + 0.5 - $y, $config->get("LeaderBoards-Z") + 0.5), $place." ".C::YELLOW.$playerName.C::GRAY." - ".C::AQUA.$killCount), [$player]);
			}
 		}
		$this->getServer()->getDefaultLevel()->addParticle(new FloatingTextParticle(new Vector3($config->get("LeaderBoards-X") + 0.5, $config->get("LeaderBoards-Y") + 0.75, $config->get("LeaderBoards-Z") + 0.5), $title), [$player]);
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		$lbcfg = new Config($this->getDataFolder()."config.yml", Config::YAML);
		if($cmd->getName() == "setlbkills"){
			if($sender instanceof Player){
				$lbcfg->set("LeaderBoards-X", $sender->getFloorX());
				$lbcfg->set("LeaderBoards-Y", $sender->getFloorY());
				$lbcfg->set("LeaderBoards-Z", $sender->getFloorZ());
				$lbcfg->save();
				$sender->sendMessage(TextFormat::GREEN."LeaderBoards spawn coordinates set in your location, please re-login...");
			} else {
				$sender->sendMessage(TextFormat::YELLOW."Please use this command in-game!");
			}
			return true;
		}
	}
}
