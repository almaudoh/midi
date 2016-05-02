<?php

namespace KodeHauz\Midi\Event\Meta;

class SmpteOffset extends MetaEvent {

  use MultiValueMetaTrait;
  use ToStringWithoutTypeTrait;

  public function __construct($time, $hours, $minutes, $seconds, $frames, $sub_frames) {
    $this->values = array($hours, $minutes, $seconds, $frames, $sub_frames);
    parent::__construct($time, 0x54, "$hours $minutes $seconds $frames $sub_frames", 'SMPTE');
  }

}
