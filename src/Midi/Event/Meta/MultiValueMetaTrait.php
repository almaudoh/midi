<?php

namespace KodeHauz\Midi\Event\Meta;

use KodeHauz\Utility\Byte;

trait MultiValueMetaTrait {

  protected $values;

  /**
   * Sequences the data bytes for this MIDI event.
   *
   * @return array
   */
  protected function sequenceDataBytes() {
    $length = count($this->values);
    $sequence = array($this->type, Byte::writeVarLen($length));
    foreach ($this->values as $value) {
      $sequence[] = $value;
    }
    return $sequence;
  }

}