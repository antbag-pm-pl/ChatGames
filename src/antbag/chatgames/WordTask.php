<?php

namespace antbag\chatgames;

use pocketmine\scheduler\Task;

class WordTask extends Task {
  
  public function __construct(Main $plugin) {
    $this->plugin = $plugin;
  }
  
  public function onRun() : void {
    $this->plugin->scrambleWord();
  }
}