<?php

namespace KodeHauz\Midi\Event\Meta;

class TimeSignature extends MetaEvent {

  use MultiValueMetaTrait;
  use ToStringWithoutTypeTrait;

  protected $values;

  public function __construct($time, $num, $den, $clocks, $beats) {
    $this->values = array($num, $den, $clocks, $beats);
    parent::__construct($time, 0x58, "$num/$den $clocks $beats", 'TimeSig');
  }

}
