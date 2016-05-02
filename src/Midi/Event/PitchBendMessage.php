<?php

namespace KodeHauz\Midi\Event;

class PitchBendMessage extends MessageEvent {

  public function __construct($time, $channel, $value) {
    // Pitch bend value cannot exceed 2^14.
    if ($value < 0 || $value > 1 << 14) {
      throw new \InvalidArgumentException('Pitch bend value must be positive and cannot exceed 16384');
    }
    $lsb = $value & 0x7f; // Bits 6..0
    $msb = ($value >> 7) & 0x7f; // Bits 13..7
    $data['l'] = $lsb;
    $data['m'] = $msb;
    parent::__construct($time, 0x0E, $channel, $data, 'Pb');
  }

}
