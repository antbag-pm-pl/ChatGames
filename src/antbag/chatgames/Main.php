<?php

namespace antbag\chatgames;

use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;

 use pocketmine\player\Player; 
 use pocketmine\Server;
 use pocketmine\event\Listener;
 use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\utils\Config;
use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener{

    public ?string $word = null;
    public array $words = [];
    public static $instance;

    public function onEnable() : void {
        if (!$this->getServer()->getPluginManager()->getPlugin("BedrockEconomy")) {
            $this->getLogger()->warning("Reward has been disabled since you do not have BedrockEconomy installed on your server.");
        }
        @mkdir($this->getDataFolder() . "topten_data");
		$this->saveResource("setting.yml");
		$this->config = (new Config($this->getDataFolder()."location.yml", Config::YAML))->getAll();
		if(empty($this->config["positions"])){
			$this->getServer()->getLogger()->Info("Please Set Location");
			return;
        
        $this->loadWords();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleDelayedTask(new WordTask($this), (20 * 60 * $this->getConfig()->get("Scramble-Time")));
        self::$instance = $this;
    }
    }

    public function onChat(playerChatEvent $event) {
        $player = $event->getPlayer();
        $msg = $event->getMessage();

        if (strtolower($msg) == strtolower($this->word)) {
            $event->cancel();
            $this->rewardPlayer($player);
            $this->loadWords();
            $this->word = null;
        }
    }


    public function loadWords() {
        foreach($this->getConfig()->get("Words") as $word) {
            $this->words[] = $word;
        }
    }
    public function rewardPlayer($player)  {
      $name = $player->getName();
      $this->getServer()->broadcastMessage("§6" . $player->getName() . " Guessed The Word Correctly.\n§6The Word Was §e" . $this->word);
      BedrockEconomyAPI::legacy()->addToPlayerBalance($player, $this->reward);
      $data = new Config($this->getDataFolder() . "topten_data/top.yml", Config::YAML);
	  $up = $data->get($name);
	  $data->set($name, $up + 1);
	  $data->save();
        
    }

    public function createtopten(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$w = $this->getConfig()->get("world");
		$world = $player->getWorld()->getDisplayName() === "$w";
		$top = $this->getConfig()->get("enable");	

		if($world){
			if($top == "true"){
				$this->getLeaderBoard();
			}
		}
	}

    public function settopdata(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();		

		$farm = new Config($this->getDataFolder() . "topten_data/top.yml", Config::YAML);
		if(!$farm->exists($name)){
			$farm->set($name, 0);
			$farm->save();
		}
	}
	
	public function getLeaderBoard(): string{
    $data = new Config($this->getDataFolder() . "topten_data/top.yml", Config::YAML);
    $setting = new Config($this->getDataFolder() . "setting.yml", Config::YAML);
    $swallet = $data->getAll();
    $message = "";
    $top = $setting->get("title-lb");
    
    if (count($swallet) > 0) {
        arsort($swallet);
        $i = 1;
        foreach ($swallet as $name => $amount) {
            $tags = str_replace(["{num}", "{player}", "{amount}"], [$i, $name, $amount], $setting->get("text-lb")) . "\n";
            $message .= "\n ".$tags;
            
            if ($i >= 10) {
                break;
            }
            ++$i;
        }
    }
	$return = (string) $top.$message;
    	return $return;
}

	public function getParticles(): array{
		return $this->particle;
	}
        

    public function scrambleWord() {
        $this->word = $this->words[array_rand($this->words)];
            $this->reward = mt_rand($this->getConfig()->get("Min-Reward"), $this->getConfig()->get("Max-Reward"));
        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player->sendMessage("§bUnscramble The Word §e". str_shuffle($this->word) ." §bWill Receive $". $this->reward ."!");
        }
        $this->getScheduler()->scheduleDelayedTask(new WordTask($this), (20 * 60 * $this->getConfig()->get("Scramble-Time")));
    }
        
    public static function getInstance(): Main{
        return self::$instance;
    }
}
