<?php

namespace antbag\chatgames;

use pocketmine\scheduler\Task;
use antbag\chatgames\Main;

class WordTask extends Task {

  public function __construct(){
        
    }
  public function onRun() : void {
    Main::getInstance()->scrambleWord();
  }
}
