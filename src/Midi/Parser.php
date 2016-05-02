<?php

namespace KodeHauz\Midi;

interface Parser {

  public function parse($string, &$timebase, $track = NULL);

}
