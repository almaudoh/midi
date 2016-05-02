<?php

namespace KodeHauz\Tests\Midi;

use KodeHauz\Midi\Event\Meta\KeySignature;
use KodeHauz\Midi\Event\Meta\MetaChannelPrefix;
use KodeHauz\Midi\Event\Meta\MetaEvent;
use KodeHauz\Midi\Event\Meta\SequenceNumber;
use KodeHauz\Midi\Event\Meta\SequencerSpecific;
use KodeHauz\Midi\Event\Meta\SmpteOffset;
use KodeHauz\Midi\Event\Meta\Tempo;
use KodeHauz\Midi\Event\Meta\Text;
use KodeHauz\Midi\Event\Meta\TimeSignature;
use KodeHauz\Midi\Event\Meta\TrackEnd;

/**
 * Tests the MIDI parser
 *
 * @group midi
 */
class ParserTestBase extends \PHPUnit_Framework_TestCase {

  public function loadSampleFile($filename) {
    $file = fopen($filename, "rb"); // Standard MIDI File, typ 0 or 1
    $file_content = fread($file, filesize($filename));
    fclose($file);
    return $file_content;
  }

}
