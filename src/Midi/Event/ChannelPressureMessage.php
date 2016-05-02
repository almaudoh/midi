<?php

namespace KodeHauz\Midi\Event;

class ChannelPressureMessage extends MessageEvent {

  public function __construct($time, $channel, $value) {
    $data['v'] = $value;
    parent::__construct($time, 0x0D, $channel, $data, 'ChPr');
  }

}