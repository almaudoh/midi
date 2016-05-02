<?php

namespace KodeHauz\Midi\Event;

class ControllerChangeMessage extends MessageEvent {

  public function __construct($time, $channel, $controller, $value) {
    $data['c'] = $controller;
    $data['v'] = $value;
    parent::__construct($time, 0x0B, $channel, $data, 'Par');
  }
}
