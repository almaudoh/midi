<?php

namespace KodeHauz\Midi\Event\Meta;

use KodeHauz\Midi\Event\MessageEvent;
use KodeHauz\Utility\Byte;

/**
 * Encapsulates a MIDI meta event.
 */
class MetaEvent extends MessageEvent {

  protected $type;
  protected $value;

  public function __construct($time, $type, $value, $mnemonic = 'Meta') {
    if ($type < 0 || $type > 127) {
      throw new \InvalidArgumentException('Meta Event type must be positive and cannot exceed 127');
    }
    $this->time = $time;
    $this->status = 0xFF;
    $this->type = $type;
    $this->value = $value;
    $this->mnemonic = $mnemonic;
    $this->data = $this->sequenceDataBytes();
  }

  public function __toString() {
    // Format as a hexadecimal in 0x00 format.
    $hex = sprintf('0x%02X', $this->type);
    return "{$this->time} {$this->mnemonic} $hex {$this->value}";
  }

  /**
   * Sequences the data bytes for this MIDI event.
   *
   * @return array
   */
  protected function sequenceDataBytes() {
    return array_merge(array($this->type), Byte::getVarLen($this->getValueLength()), $this->getValueArray());
  }

  /**
   * Returns the length of the value of this meta event.
   *
   * @return int
   */
  protected function getValueLength() {
    return empty($this->value) ? 0 : strlen($this->value);
  }

  protected function getValueArray() {
    return (array) $this->value;
  }

}
