<?php

namespace antbag\chatgames;

use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;



class Main extends PluginBase implements Listener{

    public ?string $word = null;
    public array $words = [];
    public static $instance;

    public function onEnable() : void {
        if (!$this->getServer()->getPluginManager()->getPlugin("BedrockEconomy")) {
            $this->getLogger()->warning("Reward has been disabled since you do not have BedrockEconomy installed on your server.");
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
      BedrockEconomyAPI::legacy()->addToPlayerBalance($player, $this->reward);
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
