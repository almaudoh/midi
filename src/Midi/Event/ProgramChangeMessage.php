<?php

namespace KodeHauz\Midi\Event;

class ProgramChangeMessage extends MessageEvent {

  public function __construct($time, $channel, $program) {
    $data['p'] = $program;
    parent::__construct($time, 0x0C, $channel, $data, 'PrCh');
  }

}
