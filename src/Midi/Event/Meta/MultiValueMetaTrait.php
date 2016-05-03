<?php

namespace KodeHauz\Midi\Event\Meta;

trait MultiValueMetaTrait {

  protected $values;

  protected function getValueLength() {
    return count($this->values);
  }

  protected function getValueArray() {
    return $this->values;
  }

}
