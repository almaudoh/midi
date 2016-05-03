<?php

namespace KodeHauz\Midi\Event\Meta;

use KodeHauz\Utility\Byte;

class SequencerSpecific extends MetaEvent {

  use ToStringWithoutTypeTrait;

  public function __construct($time, $data) {
    parent::__construct($time, 0x7F, $data, 'SeqSpec');
  }

  public function __toString() {
    return "{$this->time} {$this->mnemonic}{$this->value}";
  }

  public function getValueArray() {
    return Byte::stringToByteArray($this->value);
  }

}
