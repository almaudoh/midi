<?php

namespace KodeHauz\Tests\Midi;

use KodeHauz\Midi\Event\Meta\KeySignature;
use KodeHauz\Midi\Event\Meta\Tempo;
use KodeHauz\Midi\Event\Meta\TimeSignature;
use KodeHauz\Midi\Event\Meta\TrackEnd;
use KodeHauz\Midi\Event\OffMessage;
use KodeHauz\Midi\Event\OnMessage;
use KodeHauz\Midi\Event\PitchBendMessage;
use KodeHauz\Midi\Event\ProgramChangeMessage;
use KodeHauz\Midi\Event\Track;
use KodeHauz\Midi\Midi;
use KodeHauz\Midi\MidiDumper;
use KodeHauz\Midi\MidiParser;

/**
 * Tests the MIDI parser
 *
 * @group midi
 */
class MidiDumperParserTest extends \PHPUnit_Framework_TestCase {

  /**
   * @dataProvider providerDumpAndParse
   */
  public function testDumpAndParse($track) {
    $midi = new Midi();
    $midi->addTrack($track);
    $midi->setTimebase(1200);

    $dumper = new MidiDumper();
    $midi_file_contents = $dumper->dump($midi, 0);

    $parser = new MidiParser();
    $timebase = 0;
    $input_tracks = $parser->parse($midi_file_contents, $timebase);
    $this->assertEquals($input_tracks, array($track));
    $this->assertEquals(1200, $timebase);

  }

  public function providerDumpAndParse() {
    $track1 = new Track();
    $track1->addEvent(new Tempo(0, 100));

    $track2 = new Track();
    $channel = 1;
    $track2->addEvent(new OnMessage(0, $channel, 120, 100));
    $track2->addEvent(new OffMessage(200, $channel, 30, 100));
    $track2->addEvent(new OnMessage(400, $channel, 40, 100));

    $track3 = new Track();
    $track3->addEvent(new Tempo(0, 100000));
    $track3->addEvent(new TimeSignature(0, 4, 4, 240, 80));
    $track3->addEvent(new KeySignature(0, 3, 0));
    $track3->addEvent(new ProgramChangeMessage(0, 1, 20));
    $track3->addEvent(new PitchBendMessage(0, 1, 5000));
    $track3->addEvent(new OnMessage(0, 1, 34, 100));
    $track3->addEvent(new OnMessage(100, 1, 42, 80));
    $track3->addEvent(new OnMessage(1000, 1, 60, 70));
    $track3->addEvent(new OnMessage(6000, 1, 28, 90));
    $track3->addEvent(new OnMessage(8000, 1, 56, 120));
    $track3->addEvent(new TrackEnd(10000));

    return array(
      array($track1),
      array($track2),
      array($track3),
    );
  }

}
