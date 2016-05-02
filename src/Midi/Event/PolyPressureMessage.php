<?php

namespace KodeHauz\Midi\Event;

class PolyPressureMessage extends MessageEvent {

  public function __construct($time, $channel, $note, $value) {
    $data['n'] = $note;
    $data['v'] = $value;
    parent::__construct($time, 0x0A, $channel, $data, 'PoPr');
  }

}
