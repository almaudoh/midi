<?php

namespace KodeHauz\Midi\Event\Meta;

class MetaChannelPrefix extends MetaEvent {

  public function __construct($time, $type, $channel) {
    parent::__construct($time, $type, $channel);
  }

}
