<?php

namespace antbag\chatgames;

use pocketmine\scheduler\Task;
use antbag\chatgames\Main;

class WordTask extends Task {
  
  public function onRun() : void {
    Main::getInstance()->scrambleWord();
  }
}
