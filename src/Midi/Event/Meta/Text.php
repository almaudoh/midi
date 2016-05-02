<?php

namespace KodeHauz\Midi\Event\Meta;

use KodeHauz\Utility\Byte;

class Text extends MetaEvent {
  const TEXT            = 0x01;       // Meta Text
  const COPYRIGHT       = 0x02;  // Meta Copyright
  const TRACK_NAME      = 0x03;    // Meta TrackName ???sequence_name???
  const INSTRUMENT_NAME = 0x04;  // Meta InstrumentName
  const LYRIC           = 0x05;      // Meta Lyrics
  const MARKER          = 0x06;     // Meta Marker
  const CUE             = 0x07;        // Meta Cue
  const PROGRAM_NAME    = 0x08;   // Meta Program Name
  const DEVICE_NAME     = 0x09; // Meta Device (Port) Name

  static $type_names = array(
    self::TEXT            => 'Text',       // Meta Text
    self::COPYRIGHT       => 'Copyright',  // Meta Copyright
    self::TRACK_NAME      => 'TrkName',    // Meta TrackName ???sequence_name???
    self::INSTRUMENT_NAME => 'InstrName',  // Meta InstrumentName
    self::LYRIC           => 'Lyric',      // Meta Lyrics
    self::MARKER          => 'Marker',     // Meta Marker
    self::CUE             => 'Cue',        // Meta Cue
    self::PROGRAM_NAME    => 'ProgName',   // Meta Program Name
    self::DEVICE_NAME     => 'DeviceName', // Meta Device (Port) Name
  );

  public function __construct($time, $type, $text) {
    parent::__construct($time, $type, $text);
  }

  public function __toString() {
    $type_name = static::$type_names[$this->type];
    return "{$this->time} {$this->mnemonic} $type_name \"{$this->value}\"";
  }

  public function getValueArray() {
    return Byte::stringToByteArray($this->value);
  }


}
