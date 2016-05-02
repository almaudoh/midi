<?php

namespace KodeHauz\Midi\Event\Meta;

class KeySignature extends MetaEvent {

  use MultiValueMetaTrait;
  use ToStringWithoutTypeTrait;

  protected $values;

  public function __construct($time, $fifths, $minor) {
    $this->values = array($fifths, $minor);
    $scale = $minor == 0 ? 'major' : 'minor';
    parent::__construct($time, 0x59, "$fifths $scale", 'KeySig');
  }

}
