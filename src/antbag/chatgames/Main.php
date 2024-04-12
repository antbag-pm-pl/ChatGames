<?php

namespace antbag\chatgames;

use DaPigGuy\libPiggyEconomy\exceptions\MissingProviderDependencyException;
use DaPigGuy\libPiggyEconomy\exceptions\UnknownProviderException;
use DaPigGuy\libPiggyEconomy\libPiggyEconomy;
use DaPigGuy\libPiggyEconomy\providers\EconomyProvider;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener;

class Main extends PluginBase implements Listener {

    private int $reward;
    public ?string $word = null;
    public array $words = [];
    public static $instance;

    /** @var EconomyProvider */
    private $economyProvider;

    /**
     * @throws MissingProviderDependencyException
     * @throws UnknownProviderException
     */
    protected function onEnable(): void {
        self::$instance = $this;

        libPiggyEconomy::init();
        $this->economyProvider = libPiggyEconomy::getProvider($this->getConfig()->get("economy"));

        $this->loadWords();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleDelayedTask(new WordTask(), (20 * 60 * $this->getConfig()->get("Scramble-Time")));
    }

    public function getEconomyProvider(): EconomyProvider {
        return $this->economyProvider;
    }

    public function onChat(playerChatEvent $event) {
        $player = $event->getPlayer();
        $msg = $event->getMessage();

        if ($msg !== null && $this->word !== null && strtolower($msg) == strtolower($this->word)) {
            $event->cancel();
            $this->rewardPlayer($player);
            $this->loadWords();
            $this->word = null;
        }
      if (
    }


    public function loadWords() {
        foreach ($this->getConfig()->get("Words") as $word) {
            $this->words[] = $word;
        }
    }

    public function rewardPlayer($player) {
        $this->getServer()->broadcastMessage("§6" . $player->getName() . " Guessed The Word Correctly.\n§cThe Word Was " . $this->word);
        $this->getEconomyProvider()->giveMoney($player, (int) $this->reward);
    }

    public function scrambleWord() {
        $onlinePlayers = $this->getServer()->getOnlinePlayers();

        if (count($onlinePlayers) > $this->getConfig()->get("Online-Players")) {
            $this->word = $this->words[array_rand($this->words)];
            $this->reward = mt_rand($this->getConfig()->get("Min-Reward"), $this->getConfig()->get("Max-Reward"));
            foreach ($this->getServer()->getOnlinePlayers() as $player) {
                $player->sendMessage("§b Unscramble The Word §c" . str_shuffle($this->word) . " §eReceive $" . $this->reward . "!");
            }
            $this->getScheduler()->scheduleDelayedTask(new WordTask(), (20 * 60 * $this->getConfig()->get("Scramble-Time")));
        }
    }

    public static function getInstance(): Main {
        return self::$instance;
    
    }
}
