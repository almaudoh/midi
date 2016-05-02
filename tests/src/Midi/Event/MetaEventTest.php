<?php

namespace KodeHauz\Tests\Midi\Event;

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
 * Tests various MIDI meta events.
 *
 * @group midi
 */
class MetaEventTest extends \PHPUnit_Framework_TestCase {

  public function testSequenceNumber() {
    $message = new SequenceNumber(0, 131);

    $this->assertEquals((string) $message, '0 Seqnr 131');
  }

  /**
   * @dataProvider providerTextMeta
   */
  public function testTextMeta($time, $type, $text, $string_format) {
    $message = new Text($time, $type, $text);

    $this->assertEquals((string) $message, $string_format);
  }

  public function providerTextMeta() {
    return array(
      [0, Text::TEXT, 'this is some crazy text', '0 Meta Text "this is some crazy text"'],
      [3, Text::COPYRIGHT, 'this is some copyright text', '3 Meta Copyright "this is some copyright text"'],
      [7, Text::CUE, 'this is some cue text', '7 Meta Cue "this is some cue text"'],
      [3, Text::DEVICE_NAME, 'this is device name', '3 Meta DeviceName "this is device name"'],
      [9, Text::INSTRUMENT_NAME, 'this is instrument name', '9 Meta InstrName "this is instrument name"'],
      [2, Text::MARKER, 'this is a marker name', '2 Meta Marker "this is a marker name"'],
      [1, Text::TRACK_NAME, 'this is a track name', '1 Meta TrkName "this is a track name"'],
      [1, Text::PROGRAM_NAME, 'this is a program name', '1 Meta ProgName "this is a program name"'],
      [6, Text::LYRIC, 'this is a lyric', '6 Meta Lyric "this is a lyric"'],
    );
  }

  public function testMetaChannelPrefix() {
    $message = new MetaChannelPrefix(12, 0x20, 127);
    $this->assertEquals((string) $message, '12 Meta 0x20 127');

    $message = new MetaChannelPrefix(5, 0x21, 83);
    $this->assertEquals((string) $message, '5 Meta 0x21 83');
  }

  public function testTrackEnd() {
    $message = new TrackEnd(13098);
    $this->assertEquals((string) $message, '13098 Meta TrkEnd');
  }

  public function testTempo() {
    $message = new Tempo(0, 0x07A120);
    $this->assertEquals((string) $message, '0 Tempo 500000');
  }

  public function testSmpteOffset() {
    $message = new SmpteOffset(0, 2, 30, 5, 12, 67);
    $this->assertEquals((string) $message, '0 SMPTE 2 30 5 12 67');
  }

  public function testTimeSignature() {
    $message = new TimeSignature(0, 4, 4, 18, 8);
    $this->assertEquals((string) $message, '0 TimeSig 4/4 18 8');
  }

  public function testKeySignature() {
    $message = new KeySignature(23, 5, 0);
    $this->assertEquals((string) $message, '23 KeySig 5 major');
  }

  public function testSequencerSpecific() {
    $message = new SequencerSpecific(9, 50);
    $this->assertEquals((string) $message, '9 SeqSpec50');
  }

  public function testGenericMetaEvent() {
    $message = new MetaEvent(17, 0x53, 'crappy_data');
    $this->assertEquals((string) $message, '17 Meta 0x53 crappy_data');
  }

  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage Meta Event type must be positive and cannot exceed 127
   */
  public function testInvalidMetaEvent() {
    $message = new MetaEvent(0, -1, 127, 'Invalid');
  }

}
