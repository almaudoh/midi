<?php

namespace KodeHauz\Midi\Event;

class OnMessage extends MessageEvent {

  public function __construct($time, $channel, $note, $velocity) {
    if ($note > 127 || $note < 0) {
      throw new \InvalidArgumentException('Note value must be positive and cannot exceed 127');
    }
    if ($velocity > 127 || $velocity < 0) {
      throw new \InvalidArgumentException('Note velocity must be positive and cannot exceed 127');
    }
    if ($channel < 1 || $channel > 16) {
      throw new \InvalidArgumentException('Channel must be between 1 and 16');
    }
    $data['n'] = $note;
    $data['v'] = $velocity;
    parent::__construct($time, 0x09, $channel, $data, 'On');
  }

}
