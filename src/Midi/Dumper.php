<?php

namespace KodeHauz\Midi;

interface Dumper {

  /**
   * Dumps a MIDI file to a specific serialization format.
   *
   * @param \KodeHauz\Midi\Midi $midi
   *   The MIDI file to be dumped.
   * @param int $ttype
   *
   * @return mixed
   *   The MIDI file in the encoded format.
   */
  public function dump(Midi $midi, $ttype);

}
