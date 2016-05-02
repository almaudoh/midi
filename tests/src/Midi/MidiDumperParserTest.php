<?php

namespace KodeHauz\Tests\Midi;

use KodeHauz\Midi\Event\Meta\Tempo;
use KodeHauz\Midi\Event\OffMessage;
use KodeHauz\Midi\Event\OnMessage;
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

  public function _testDumpParseOnMessage($track) {
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
    $track2->addEvent(new OffMessage(20, $channel, 30, 100));
    $track2->addEvent(new OnMessage(40, $channel, 40, 100));

    return array(
//      array($track1),
      array($track2),
    );
  }

}
