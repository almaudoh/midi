<?php

namespace KodeHauz\Tests\Midi;

use KodeHauz\Midi\Event\ChannelPressureMessage;
use KodeHauz\Midi\Event\ControllerChangeMessage;
use KodeHauz\Midi\Event\Event;
use KodeHauz\Midi\Event\Meta\KeySignature;
use KodeHauz\Midi\Event\Meta\MetaChannelPrefix;
use KodeHauz\Midi\Event\Meta\SequenceNumber;
use KodeHauz\Midi\Event\Meta\SequencerSpecific;
use KodeHauz\Midi\Event\Meta\SmpteOffset;
use KodeHauz\Midi\Event\Meta\Tempo;
use KodeHauz\Midi\Event\Meta\Text;
use KodeHauz\Midi\Event\Meta\TimeSignature;
use KodeHauz\Midi\Event\Meta\TrackEnd;
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
use KodeHauz\Utility\Byte;

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
      array(new SequenceNumber(0), array(0xFF, 0x00, 0x00)),
      array(new SequenceNumber(0, 4200), array(0xFF, 0x00, 0x02, 0x10, 0x68)),
      array(new SequencerSpecific(0, 'Proprietary data...'), array(0xFF, 0x7F, 0x13, 0x50, 0x72, 0x6F, 0x70, 0x72, 0x69, 0x65, 0x74, 0x61, 0x72, 0x79, 0x20, 0x64, 0x61, 0x74, 0x61, 0x2E, 0x2E, 0x2E)),
      array(new MetaChannelPrefix(0, MetaChannelPrefix::DEVICE, 90), array(0xFF, 0x20, 0x01, 0x5A)),
      array(new MetaChannelPrefix(0, MetaChannelPrefix::PORT, 120), array(0xFF, 0x21, 0x01, 0x78)),
      array(new Tempo(0, 500000), array(0xFF, 0x51, 0x03, 0x07, 0xA1, 0x20)),
      array(new Text(0, Text::TEXT, 'Hello text'), array(0xFF, 0x01, 0x0A, 0x48, 0x65, 0x6C, 0x6C, 0x6F, 0x20, 0x74, 0x65, 0x78, 0x74)),
      array(new Text(0, Text::LYRIC, 'This is the time...'), array(0xFF, 0x05, 0x13, 0x54, 0x68, 0x69, 0x73, 0x20, 0x69, 0x73, 0x20, 0x74, 0x68, 0x65, 0x20, 0x74, 0x69, 0x6D, 0x65, 0x2E, 0x2E, 0x2E)),
      array(new Text(0, Text::COPYRIGHT, 'Copyright @2016'), array(0xFF, 0x02, 0x0F, 0x43, 0x6F, 0x70, 0x79, 0x72, 0x69, 0x67, 0x68, 0x74, 0x20, 0x40, 0x32, 0x30, 0x31, 0x36)),
      array(new TrackEnd(0), array(0xFF, 0x2F, 0x00)),
      array(new SmpteOffset(0, 20, 34, 55, 10, 25), array(0xFF, 0x54, 0x05, 0x14, 0x22, 0x37, 0x0A, 0x19)),
      array(new TimeSignature(0, 6, 8, 18, 8), array(0xFF, 0x58, 0x04, 0x06, 0x03, 0x12, 0x08)),
      array(new KeySignature(0, -7, 1), array(0xFF, 0x59, 0x02, 0xF9, 0x01)),
      array(new KeySignature(0, -2, 0), array(0xFF, 0x59, 0x02, 0xFE, 0x00)),
      array(new KeySignature(0, 4, 1), array(0xFF, 0x59, 0x02, 0x04, 0x01)),
      array(new KeySignature(0, 0, 0), array(0xFF, 0x59, 0x02, 0x00, 0x00)),
    );
  }

  public function testDumpTrack() {
    $track = new Track();
    $track->addEvent(new Tempo(0, 500000));
    $track->addEvent(new OnMessage(10, 1, 25, 25));
    $track->addEvent(new TrackEnd(1200));

    $reflection = new \ReflectionClass(MidiDumper::class);
    $method = $reflection->getMethod('dumpTrack');
    $method->setAccessible(TRUE);
    $dump = $method->invoke(new MidiDumper(), $track);
    $expected = 'MTrk' . Byte::intToBinaryString(16, 4)
      . Byte::writeVarLen(0) . Byte::hexToBinaryString('FF510307A120') // new Tempo(0, 500000)
      . Byte::writeVarLen(10) . Byte::hexToBinaryString('901919')      // new OnMessage(10, 1, 25, 25)
      . Byte::writeVarLen(1190) . Byte::hexToBinaryString('FF2F00');   // new TrackEnd(1200)
    $this->assertEquals($expected, $dump);

    $midi = "MThd\0\0\0\6\0\1\0\1" . Byte::hexToBinaryString('E728') . $dump;

    $parser = new MidiParser();
    $track1 = $parser->parse($midi, $timebase, 0);
    $this->assertEquals("0 Tempo 500000\n10 On ch=1 n=25 v=25\n1200 Meta TrkEnd", (string) $track1[0]);
  }

  public function testDumpToFile() {
    $midi = new Midi();
    $track = $this->getMidiTrack();
    $midi->addTrack($track);
    $midi->setTimebase(1200);

    $dumper = new MidiDumper();
    $midi_file_contents = $dumper->dump($midi, 0);
    $file = fopen(__DIR__ . '/../../output/test.mid', 'w');
    fwrite($file, $midi_file_contents);
    $file = fopen(__DIR__ . '/../../output/test.mid', 'r');
    $input_file_contents = fread($file, 10240);
    fclose($file);
    $this->assertEquals($midi_file_contents, $input_file_contents);

    $parser = new MidiParser();
    $timebase = 0;
    $input_tracks = $parser->parse($input_file_contents, $timebase);
    $this->assertEquals($input_tracks, array($track));
    $this->assertEquals(1200, $timebase);
  }

  protected function getMidiTrack() {
    $track3 = new Track();
    $track3->addEvent(new Tempo(0, 500000));
    $track3->addEvent(new TimeSignature(0, 4, 4, 240, 80));
    $track3->addEvent(new KeySignature(0, 3, 0));
    $track3->addEvent(new ProgramChangeMessage(0, 1, 20));
    $track3->addEvent(new PitchBendMessage(0, 1, 5000));
    $track3->addEvent(new PolyPressureMessage(0, 1, 60, 0));
    $track3->addEvent(new OnMessage(0, 1, 40, 100));
    $track3->addEvent(new OnMessage(100, 1, 50, 80));
    $track3->addEvent(new OnMessage(1000, 1, 60, 100));
    $track3->addEvent(new OnMessage(5000, 1, 70, 100));
    $track3->addEvent(new OnMessage(10000, 1, 80, 120));
    $track3->addEvent(new TrackEnd(20000));
    return $track3;
  }

}
