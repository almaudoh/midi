<?php

namespace KodeHauz\Midi\Event\Meta;

class MetaChannelPrefix extends MetaEvent {

  const DEVICE = 0x20;
  const PORT = 0x21;

  public function __construct($time, $type, $channel) {
    parent::__construct($time, $type, $channel);
  }

  public function getValueLength() {
    return 1;
  }

}
