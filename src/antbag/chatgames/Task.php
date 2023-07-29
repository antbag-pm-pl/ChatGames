<?php

namespace antbag\chatgames\Task;

use pocketmine\scheduler\Task;

class Task extends Task {
  
  public function __construct(Main $plugin) {
    $this->plugin = $plugin;
  }
  
  public function onRun() : void {
    $this->plugin->scrambleWord();
  }
}