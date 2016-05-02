<?php

namespace KodeHauz\Tests\Midi;

use KodeHauz\Midi\Event\Meta\Tempo;
use KodeHauz\Midi\Event\Track;
use KodeHauz\Midi\MidiParser;

/**
 * Tests the MIDI parser
 *
 * @group midi
 */
class MidiParserTest extends ParserTestBase {

  public function testSomething() {
    $midi_string = $this->loadSampleFile(__DIR__ . '/../../sample_files/beethoven1.mid');

    $parser = new MidiParser();
    $timebase = 0;
    $tracks = $parser->parse($midi_string, $timebase);

    $this->assertEquals($timebase, 120);
    $this->assertEquals(count($tracks), 1);

    // Track 0 should be a meta track.
    $this->assertTrackEvent($tracks[0], Tempo::class);

    $n = 5;
    for ($i = 0; $i < max($n, count($tracks)); $i++) {
      echo "Track $i:\n";
      echo $tracks[$i];
      echo "\n\n";
    }
  }

  public function _testSequenceNumber() {
    $message = new SequenceNumber(0, 131);

    $this->assertEquals((string) $message, '0 Seqnr 131');
  }

  /**
   * @dataProvider providerTextMeta
   */
  public function _testTextMeta($time, $type, $text, $string_format) {
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

  public function assertTrackNumber() {

  }

  public function assertTrackEvent(Track $track, $event_class) {
    // @todo Proper implementation of this is needed.
    foreach ($track->getAllEvents() as $event) {
      if ($event instanceof $event_class) {
        $this->assertTrue(true);
        return;
      }
    }
    $this->assertFalse(true);
  }

}
