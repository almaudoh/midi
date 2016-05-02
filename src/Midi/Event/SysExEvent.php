<?php

namespace KodeHauz\Midi\Event;

class SysExEvent extends MessageEvent {

  protected $value;

  public function __construct($time, $value) {
    $this->time = $time;
    $this->status = 0xF0;
    $this->value = $value;
    $this->mnemonic = 'SysEx';
    $this->data = array();
  }

}
