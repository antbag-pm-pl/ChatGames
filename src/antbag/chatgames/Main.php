<?php

namespace antbag\chatgames;

use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use onebone\economyapi\EconomyAPI;
use pocketmine\Server;

class Main extends PluginBase implements Listener{

    public ?string $word = null;
    public array $words = [];
    public static $instance;
    private $EconomyAPI = false;
    private $BedrockEconomy = false;

    public function onEnable() : void {
      if($this->getConfig()->get("EconomyAPI") == true && $this->getConfig()->get("BedrockEconomy") == true) {
        $this->getLogger()->critical("EconomyAPI and BedrockEconomy are both set to true this will cuase errors therefore, the plugin is disabling");
        $this->getServer()->getPluginManager()->disablePlugin($this);
        return;
      }
      
        $this->loadWords();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleDelayedTask(new WordTask($this), (20 * 60 * $this->getConfig()->get("Scramble-Time")));
      self::$instance = $this;
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
      if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null && $this->getConfig()->get("BedrockEconomy") == true) {
        BedrockEconomyAPI::legacy()->addToPlayerBalance($player, $this->reward);
      } elseif ($this->getServer()->getPluginManager()->getPlugin("BedrockEconomy") != null && $this->getConfig()->get("EconomyAPI") == true) {
        EconomyAPI::getInstance()->addMoney($player, $this->reward);
      } else {
        Server::getInstance()->broadcastMessage("No Economy is loaded");
      }
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
