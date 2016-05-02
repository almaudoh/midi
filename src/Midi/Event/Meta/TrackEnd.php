<?php

namespace KodeHauz\Midi\Event\Meta;

class TrackEnd extends MetaEvent {

  public function __construct($time) {
    parent::__construct($time, 0x2F, NULL);
  }

  public function __toString() {
    return "{$this->time} {$this->mnemonic} TrkEnd";
  }

}
