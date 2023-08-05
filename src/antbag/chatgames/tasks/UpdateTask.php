<?php

namespace antbag\tasks;

use pocketmine\scheduler\Task;

class UpdateTask extends Task{

    public function __construct(Main $pl){
     $this->pl = $pl;
    }

    public function onRun(): void{
     $lb = $this->pl->getLeaderBoard();
     $list = $this->pl->getParticles();
     foreach($list as $particle){
      $particle->setText($lb);
     }
    }

}
