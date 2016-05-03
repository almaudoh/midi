<?php

namespace KodeHauz\Midi\Event\Meta;

class SequenceNumber extends MetaEvent {

  use ToStringWithoutTypeTrait;

  public function __construct($time, $number = 0) {
    if ($number > 65535) {
      throw new \InvalidArgumentException('Sequence number cannot exceed 65535');
    }
    parent::__construct($time, 0x00, $number, 'Seqnr');
  }

  /**
   * {@inheritdoc}
   */
  protected function getValueLength() {
    return $this->value ? 2 : 0;
  }

  protected function getValueArray() {
    if (empty($this->value)) {
      return array();
    }
    $values = array();
    $values[] = ($this->value & 0xFF00) >> 8;
    $values[] = $this->value & 0x00FF;
    return $values;
  }

}
