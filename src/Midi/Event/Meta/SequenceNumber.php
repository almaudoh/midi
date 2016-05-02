<?php

namespace KodeHauz\Midi\Event\Meta;

class SequenceNumber extends MetaEvent {

  use ToStringWithoutTypeTrait;

  public function __construct($time, $number) {
    parent::__construct($time, 0x00, $number, 'Seqnr');
  }

}
