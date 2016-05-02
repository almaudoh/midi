<?php

namespace KodeHauz\Tests\Midi;

use KodeHauz\Midi\Event\ChannelPressureMessage;
use KodeHauz\Midi\Event\ControllerChangeMessage;
use KodeHauz\Midi\Event\Event;
use KodeHauz\Midi\Event\Meta\Tempo;
use KodeHauz\Midi\Event\Meta\Text;
use KodeHauz\Midi\Event\OffMessage;
use KodeHauz\Midi\Event\OnMessage;
use KodeHauz\Midi\Event\PitchBendMessage;
use KodeHauz\Midi\Event\PolyPressureMessage;
use KodeHauz\Midi\Event\ProgramChangeMessage;
use KodeHauz\Midi\Event\SysExEvent;
use KodeHauz\Midi\Event\Track;
use KodeHauz\Midi\Midi;
use KodeHauz\Midi\MidiDumper;
use KodeHauz\Midi\MidiParser;

/**
 * Tests the MIDI parser
 *
 * @group midi
 */
class MidiDumperTest extends \PHPUnit_Framework_TestCase {

  protected $reflectionMethod;

  public function setUp() {
    $reflection = new \ReflectionClass(MidiDumper::class);
    $this->reflectionMethod = $reflection->getMethod('dumpEvent');
    $this->reflectionMethod->setAccessible(TRUE);
  }

  /**
   * @dataProvider providerDumpEvent
   */
  public function testDumpEvent(Event $event, array $expected) {
    $dumper = new MidiDumper();
    $expected_string = '';
    foreach ($expected as $byte) {
      $expected_string .= chr($byte);
    }
    $dump = $this->reflectionMethod->invoke($dumper, $event);
    $this->assertEquals($expected_string, $dump);
  }

  public function providerDumpEvent() {
    return array(
      array(new OnMessage(0, 1, 100, 100), array(0x90, 0x64, 0x64)),
      array(new OnMessage(0, 1, 68, 32),  array(0x90, 0x44, 0x20)),
      array(new OnMessage(0, 1, 123, 81), array(0x90, 0x7B, 0x51)),
      array(new OffMessage(0, 1, 100, 100), array(0x80, 0x64, 0x64)),
      array(new OffMessage(0, 1, 68, 32),  array(0x80, 0x44, 0x20)),
      array(new OffMessage(0, 1, 123, 81), array(0x80, 0x7B, 0x51)),
      array(new PolyPressureMessage(0, 3, 42, 80), array(0xA2, 0x2A, 0x50)),
      array(new ControllerChangeMessage(0, 6, 68, 96), array(0xB5, 0x44, 0x60)),
      array(new ChannelPressureMessage(0, 10, 100), array(0xD9, 0x64)),
      array(new PitchBendMessage(0, 8, 8192), array(0xE7, 0x00, 0x40)),
      array(new ProgramChangeMessage(0, 16, 90), array(0xCF, 0x5A)),
//      array(new SysExEvent(0, 80), array(0xF0, 0x00, 0x50)),
      array(new Tempo(0, 500000), array(0xFF, 0x51, 0x03, 0x07, 0xA1, 0x20)),
      array(new Text(0, Text::TEXT, 'Hello text'), array(0xFF, 0x01, 0x0A, 0x48, 0x65, 0x6C, 0x6C, 0x6F, 0x20, 0x74, 0x65, 0x78, 0x74)),
      array(new Text(0, Text::LYRIC, 'This is the time...'), array(0xFF, 0x05, 0x13, 0x54, 0x68, 0x69, 0x73, 0x20, 0x69, 0x73, 0x20, 0x74, 0x68, 0x65, 0x20, 0x74, 0x69, 0x6D, 0x65, 0x2E, 0x2E, 0x2E)),
      array(new Text(0, Text::COPYRIGHT, 'Copyright @2016'), array(0xFF, 0x02, 0x0F, 0x43, 0x6F, 0x70, 0x79, 0x72, 0x69, 0x67, 0x68, 0x74, 0x20, 0x40, 0x32, 0x30, 0x31, 0x36)),
//      array(new Tempo(0, 500000), array(0xFF, 0x51, 0x03, 0x07, 0xA1, 0x20)),
//      array(new Tempo(0, 500000), array(0xFF, 0x51, 0x03, 0x07, 0xA1, 0x20)),
    );
  }


  public function _testDumpToFile() {
    $track = new Track();
    $channel = 0;
    $track->addEvent(new Tempo(0, 100));

    $midi = new Midi();
    $midi->addTrack($track);
    $midi->setTimebase(1200);

    $dumper = new MidiDumper();
    $midi_file_contents = $dumper->dump($midi, 0);
    $file = fopen(__DIR__ . '/../../output/test.mid', 'rw');
    fwrite($file, $midi_file_contents);

    $input_file_contents = fread($file, 1024);
    fclose($file);

    $parser = new MidiParser();
    $timebase = 0;
    $input_tracks = $parser->parse($input_file_contents, $timebase);
    $this->assertEquals($input_tracks, array($track));
    $this->assertEquals(1200, $timebase);

  }



}
