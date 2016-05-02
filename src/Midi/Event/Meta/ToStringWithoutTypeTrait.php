<?php

namespace KodeHauz\Midi\Event\Meta;

trait ToStringWithoutTypeTrait {

  public function __toString() {
    return "{$this->time} {$this->mnemonic} {$this->value}";
  }

}
