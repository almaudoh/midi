<?php

namespace KodeHauz\Midi\Event\Meta;

class Tempo extends MetaEvent {

  use ToStringWithoutTypeTrait;

  public function __construct($time, $tempo) {
    parent::__construct($time, 0x51, $tempo, 'Tempo');
  }

  /**
   * {@inheritdoc}
   */
  protected function getValueLength() {
    return 3;
  }

  protected function getValueArray() {
    $values = array();
    $values[] = $this->value >> 16;
    $values[] = ($this->value & 0x00FF00) >> 8;
    $values[] = $this->value & 0x0000FF;
    return $values;
  }

  public static function fromBpm() {

  }

}
