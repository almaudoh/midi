<?php

namespace KodeHauz\Midi;

use KodeHauz\Midi\Event\ChannelPressureMessage;
use KodeHauz\Midi\Event\ControllerChangeMessage;
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
use KodeHauz\Midi\Event\OffMessage;
use KodeHauz\Midi\Event\OnMessage;
use KodeHauz\Midi\Event\PitchBendMessage;
use KodeHauz\Midi\Event\PolyPressureMessage;
use KodeHauz\Midi\Event\ProgramChangeMessage;
use KodeHauz\Midi\Event\SysExEvent;
use KodeHauz\Midi\Event\Track;
use KodeHauz\Utility\Byte;

class MidiParser implements Parser {

  /**
   * Parses a loaded MIDI binary string into tracks and updates the metadata.
   *
   * @param $midi_string
   *   The loaded MIDI SMF string.
   * @param $trackNum
   *   The specific track number to parse from the MIDI file. If not specified,
   *   all tracks will be parsed.
   *
   * @return \KodeHauz\Midi\Event\Track[]
   */
  public function parse($midi_string, &$timebase, $trackNum = NULL) {
    if (strpos($midi_string, 'MThd') > 0) {
      // Get rid of RMID header.
      $midi_string = substr($midi_string, strpos($midi_string, 'MThd'));
    }

    $header = substr($midi_string, 0, 14);
    if (substr($header, 0, 8) != "MThd\0\0\0\6") {
      throw new MidiParserException('wrong MIDI-header');
    }
    $type = ord($header[9]);
    if ($type > 1) {
      throw new MidiParserException('only SMF Types 0 and 1 are supported');
    }
    //$trackCnt = ord($header[10])*256 + ord($header[11]); //ignore
    $timebase = ord($header[12]) * 256 + ord($header[13]);
    //$this->midi->type = $type;
    // @todo Remove all references to the Midi object.
    $trackStrings = explode('MTrk', $midi_string);
    array_shift($trackStrings);
    $track_count = count($trackStrings);
    $tracks = array();
    if (isset($trackNum) && $trackNum > 0) {
      if ($trackNum >= $track_count) {
        throw new MidiParserException('SMF has less tracks than $track');
      }
      $tracks[] = $this->parseTrack($trackStrings[$trackNum], $trackNum);
    }
    else {
      foreach ($trackStrings as $i => $trackString) {
        $tracks[] = $this->parseTrack($trackString, $i);
      }
    }
    return $tracks;
  }

  /**
   * Converts a binary track string to track (list of msg strings).
   */
  protected function parseTrack($binStr, $trackNum) {
    $trackLen = strlen($binStr);
    $p = 4;
    $time = 0;
    $events = array();
//    $last = '';
    while ($p < $trackLen) {
      // timedelta
      $dt = $this->readVarLen($binStr, $p);
      $time += $dt;
      $byte = ord($binStr[$p]);
      $high = $byte >> 4;
      $low = $byte - $high * 16;
      switch ($high) {
        case 0x0C: //PrCh = ProgramChange
          $chan = $low + 1;
          $prog = ord($binStr[$p + 1]);
          $last = 'PrCh';
          //$track[] = "$time PrCh ch=$chan p=$prog";
          $events[] = new ProgramChangeMessage($time, $chan, $prog);
          $p += 2;
          break;
        case 0x09: //On
          $chan = $low + 1;
          $note = ord($binStr[$p + 1]);
          $vel = ord($binStr[$p + 2]);
          $last = 'On';
          //$track[] = "$time On ch=$chan n=$note v=$vel";
          $events[] = new OnMessage($time, $chan, $note, $vel);
          $p += 3;
          break;
        case 0x08: //Off
          $chan = $low + 1;
          $note = ord($binStr[$p + 1]);
          $vel = ord($binStr[$p + 2]);
          $last = 'Off';
          //$track[] = "$time Off ch=$chan n=$note v=$vel";
          $events[] = new OffMessage($time, $chan, $note, $vel);
          $p += 3;
          break;
        case 0x0A: //PoPr = PolyPressure
          $chan = $low + 1;
          $note = ord($binStr[$p + 1]);
          $val = ord($binStr[$p + 2]);
          $last = 'PoPr';
          //$track[] = "$time PoPr ch=$chan n=$note v=$val";
          $events[] = new PolyPressureMessage($time, $chan, $note, $val);
          $p += 3;
          break;
        case 0x0B: //Par = ControllerChange
          $chan = $low + 1;
          $c = ord($binStr[$p + 1]);
          $val = ord($binStr[$p + 2]);
          $last = 'Par';
          //$track[] = "$time Par ch=$chan c=$c v=$val";
          $events[] = new ControllerChangeMessage($time, $chan, $c, $val);
          $p += 3;
          break;
        case 0x0D: //ChPr = ChannelPressure
          $chan = $low + 1;
          $val = ord($binStr[$p + 1]);
          $last = 'ChPr';
          //$track[] = "$time ChPr ch=$chan v=$val";
          $events[] = new ChannelPressureMessage($time, $chan, $val);
          $p += 2;
          break;
        case 0x0E: //Pb = PitchBend
          $chan = $low + 1;
          $val = (ord($binStr[$p + 1]) & 0x7F) | (((ord($binStr[$p + 2])) & 0x7F) << 7);
          $last = 'Pb';
          //$track[] = "$time Pb ch=$chan v=$val";
          $events[] = new PitchBendMessage($time, $chan, $val);
          $p += 3;
          break;
        default:
          switch ($byte) {
            case 0xFF: // Meta
              $meta = ord($binStr[$p + 1]);
              switch ($meta) {
                case 0x00: // sequence_number
                  $tmp = ord($binStr[$p + 2]);
                  if ($tmp == 0x00) {
                    $num = $trackNum;
                    $p += 3;
                  }
                  else {
                    $num = 1;
                    $p += 5;
                  }
                  //$track[] = "$time Seqnr $num";
                  $events[] = new SequenceNumber($time, $num);
                  break;

                case 0x01: // Meta Text
                case 0x02: // Meta Copyright
                case 0x03: // Meta TrackName ???sequence_name???
                case 0x04: // Meta InstrumentName
                case 0x05: // Meta Lyrics
                case 0x06: // Meta Marker
                case 0x07: // Meta Cue
                case 0x08: // Meta Program Name
                case 0x09: // Meta Device (Port) Name
                  $texttypes = array(
                    'Text',
                    'Copyright',
                    'TrkName',
                    'InstrName',
                    'Lyric',
                    'Marker',
                    'Cue',
                    'ProgName',
                    'DeviceName'
                  );
                  $type = $texttypes[$meta - 1];
                  $p += 2;
                  $len = $this->readVarLen($binStr, $p);
                  if (($len + $p) > $trackLen) {
                    throw new MidiParserException("Meta $type has corrupt variable length field ($len) [track: $trackNum dt: $dt]");
                  }
                  $txt = substr($binStr, $p, $len);
                  //$track[] = "$time Meta $type \"$txt\"";
                  $events[] = new Text($time, $meta, $txt);
                  $p += $len;
                  break;
                case 0x20: // ChannelPrefix
                  if (ord($binStr[$p + 2]) == 0) {
                    $p += 3;
                  }
                  else {
                    $chan = ord($binStr[$p + 3]);
                    if ($chan < 10) {
                      $chan = '0' . $chan;
                    }//???
                    $last = 'MetaChannelPrefix';
                    //$track[] = "$time Meta 0x20 $chan";
                    $events[] = new MetaChannelPrefix($time, $meta, $chan);
                    $p += 4;
                  }
                  break;
                case 0x21: // ChannelPrefixOrPort
                  if (ord($binStr[$p + 2]) == 0) {
                    $p += 3;
                  }
                  else {
                    $chan = ord($binStr[$p + 3]);
                    if ($chan < 10) {
                      $chan = '0' . $chan;
                    }//???
                    //$track[] = "$time Meta 0x21 $chan";
                    $events[] = new MetaChannelPrefix($time, $meta, $chan);
                    $p += 4;
                  }
                  break;
                case 0x2F: // Meta TrkEnd
                  //$track[] = "$time Meta TrkEnd";
                  $events[] = new TrackEnd($time);
                  return new Track($events);//ignore rest
                case 0x51: // Tempo
                  $tempo = ord($binStr[$p + 3]) * 256 * 256 + ord($binStr[$p + 4]) * 256 + ord($binStr[$p + 5]);
                  //$track[] = "$time Tempo $tempo";
                  $events[] = new Tempo($time, $tempo);
                  if ($trackNum == 0 && $time == 0) {
                    $this->tempo = $tempo;// ???
                    $this->tempoMsgNum = count($events) - 1;
                  }
                  $p += 6;
                  break;
                case 0x54: // SMPTE offset
                  $len = ord($binStr[$p + 2]); # should be: 0x05 => $p+=8;
                  $h = $len > 0 ? ord($binStr[$p + 3]) : 0;
                  $m = $len > 1 ? ord($binStr[$p + 4]) : 0;
                  $s = $len > 2 ? ord($binStr[$p + 5]) : 0;
                  $f = $len > 3 ? ord($binStr[$p + 6]) : 0;
                  $fh = $len > 4 ? ord($binStr[$p + 7]) : 0;
                  //$track[] = "$time SMPTE $h $m $s $f $fh";
                  $events[] = new SmpteOffset($time, $h, $m, $s, $f, $fh);
                  $p += (3 + $len);
                  break;
                case 0x58: // TimeSig
                  $z = ord($binStr[$p + 3]);
                  $t = pow(2, ord($binStr[$p + 4]));
                  $mc = ord($binStr[$p + 5]);
                  $c = ord($binStr[$p + 6]);
                  //$track[] = "$time TimeSig $z/$t $mc $c";
                  $events[] = new TimeSignature($time, $z, $t, $mc, $c);
                  $p += 7;
                  break;
                case 0x59: // KeySig
                  $len = ord($binStr[$p + 2]); # should be: 0x02 => $p+=5
                  $vz = $len > 0 ? ord($binStr[$p + 3]) : 0;
                  $minor = ord($binStr[$p + 4]);
                  $g = ($len <= 1 || $minor  == 0) ? 'major' : 'minor';
                  #$g = ord($binStr[$p+4])==0?'major':'minor';
                  //$track[] = "$time KeySig $vz $g";
                  $events[] = new KeySignature($time, $vz, $minor);
                  $p += (3 + $len);
                  break;
                case 0x7F: // Sequencer specific data (string or hexString???)
                  $p += 2;
                  $len = $this->readVarLen($binStr, $p);
                  if (($len + $p) > $trackLen) {
                    throw new MidiParserException("SeqSpec has corrupt variable length field ($len) [track: $trackNum dt: $dt]");
                  }
                  $p -= 3;
                  $data = '';
                  for ($i = 0; $i < $len; $i++) {
                    $data .= ' ' . sprintf("%02x", ord($binStr[$p + 3 + $i]));
                  }
                  //$track[] = "$time SeqSpec$data";
                  $events[] = new SequencerSpecific($time, $data);
                  $p += $len + 3;
                  break;

                default:
                  $meta2 = ord($binStr[$p + 1]);
                  $metacode = sprintf("%02x", $meta2);
                  $p += 2;
                  $len = $this->readVarLen($binStr, $p);
                  if (($len + $p) > $trackLen) {
                    throw new MidiParserException("Meta $metacode has corrupt variable length field ($len) [track: $trackNum dt: $dt]");
                  }
                  $p -= 3;
                  $data = '';
                  for ($i = 0; $i < $len; $i++) {
                    $data .= ' ' . sprintf("%02x", ord($binStr[$p + 3 + $i]));
                  }
                  //$track[] = "$time Meta 0x$metacode $data";
                  $events[] = new MetaEvent($time, $meta2, $data);
                  $p += $len + 3;
                  break;
              } // switch ($meta)
              break; // Ende Meta

            case 0xF0: // SysEx
              $p += 1;
              $len = $this->readVarLen($binStr, $p);
              if (($len + $p) > $trackLen) {
                throw new MidiParserException("SysEx has corrupt variable length field ($len) [track: $trackNum dt: $dt p: $p]");
              }
              $str = 'f0';
              #for ($i=0;$i<$len;$i++) $str.=' '.sprintf("%02x",ord($binStr[$p+2+$i]));
              for ($i = 0; $i < $len; $i++) {
                $str .= ' ' . sprintf("%02x", ord($binStr[$p + $i]));
              }
              //$track[] = "$time SysEx $str";
              $events[] = new SysExEvent($time, $str);
              $p += $len;
              break;
            default: // Repetition of last event?
              switch ($last) {
                case 'On':
                  $note = ord($binStr[$p]);
                  $vel = ord($binStr[$p + 1]);
                  //$track[] = "$time $last ch=$chan n=$note v=$vel";
                  $events[] = new OnMessage($time, $chan, $note, $vel);
                  $p += 2;
                  break;
                case 'Off':
                  $note = ord($binStr[$p]);
                  $vel = ord($binStr[$p + 1]);
                  //$track[] = "$time $last ch=$chan n=$note v=$vel";
                  $events[] = new OffMessage($time, $chan, $note, $vel);
                  $p += 2;
                  break;
                case 'PrCh':
                  $prog = ord($binStr[$p]);
                  //$track[] = "$time PrCh ch=$chan p=$prog";
                  $events[] = new ProgramChangeMessage($time, $chan, $prog);
                  $p += 1;
                  break;
                case 'PoPr':
                  $note = ord($binStr[$p + 1]);
                  $val = ord($binStr[$p + 2]);
                  //$track[] = "$time PoPr ch=$chan n=$note v=$val";
                  $events[] = new PolyPressureMessage($time, $chan, $note, $val);
                  $p += 2;
                  break;
                case 'ChPr':
                  $val = ord($binStr[$p]);
                  //$track[] = "$time ChPr ch=$chan v=$val";
                  $events[] = new ChannelPressureMessage($time, $chan, $val);
                  $p += 1;
                  break;
                case 'Par':
                  $c = ord($binStr[$p]);
                  $val = ord($binStr[$p + 1]);
                  //$track[] = "$time Par ch=$chan c=$c v=$val";
                  $events[] = new ControllerChangeMessage($time, $chan, $c, $val);
                  $p += 2;
                  break;
                case 'Pb':
                  $val = (ord($binStr[$p]) & 0x7F) | ((ord($binStr[$p + 1]) & 0x7F) << 7);
                  //$track[] = "$time Pb ch=$chan v=$val";
                  $events[] = new PitchBendMessage($time, $chan, $val);
                  $p += 2;
                  break;
                case 'MetaChannelPrefix':
                  $last = 'MetaChannelPrefix';
                  //$track[] = "$time Meta 0x20 $chan";
                  $events[] = new MetaChannelPrefix($time, '0x20', $chan);
                  $p += 3;
                  break;
                default:
// MM: ToDo: Repetition of SysEx and META-events? with <last>?? \n";
                  throw new MidiParserException("unknown repetition: $last");

              }  // switch ($last)
          } // switch ($byte)
      } // switch ($high)
    } // while
    return new Track($events);
  }

  protected function readVarLen($str, &$pos) {
    return Byte::readVarLen($str, $pos);
  }

  protected function writeVarLen($value) {
    return Byte::writeVarLen($value);
  }

}
