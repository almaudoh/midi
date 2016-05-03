<?php

namespace KodeHauz\Midi\Event\Meta;

class TimeSignature extends MetaEvent {

  use MultiValueMetaTrait;
  use ToStringWithoutTypeTrait;

  protected $values;

  /**
   * @param $time
   * @param $num
   * @param int $den
   *   The value of the denominator in the time signature, i.e. 2 for 2/2 timing,
   *   4 for 4/4 timing, 8 for 3/8 timing, etc. NB: in MIDI, $den is converted
   *   to the negative power to which 2 is raised to obtain the note value.
   * @param string $clocks
   * @param $beats
   */
  public function __construct($time, $num, $den, $clocks, $beats) {
    $power = log($den, 2);
    $this->values = array($num, $power, $clocks, $beats);
    parent::__construct($time, 0x58, "$num/$den $clocks $beats", 'TimeSig');
  }

}
