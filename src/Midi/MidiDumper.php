<?php

namespace KodeHauz\Midi;

use KodeHauz\Midi\Event\Event;
use KodeHauz\Midi\Event\Track;
use KodeHauz\Utility\Byte;

class MidiDumper implements Dumper {

  public function dump(Midi $midi, $ttype) {
    $track_count = $midi->getTrackCount();
    $type = ($track_count > 1) ? 1 : 0;
    $midStr = "MThd\0\0\0\6\0" . chr($type) . Byte::getBytes($track_count, 2) . Byte::getBytes($midi->getTimeBase(), 2);
    foreach ($midi->getTracks() as $track) {
      $midStr .= $this->dumpTrack($track);
    }
    return $midStr;
  }

  protected function dumpTrack(Track $track) {
    $time = 0;
    $dump = "MTrk";
    $trackStart = strlen($dump);

    $last = '';

    foreach ($track->getAllEvents() as $event) {
      $t = $event->getTime();
      $dt = $t - $time;

      // A: IGNORE EVENTS WITH INCORRECT TIMESTAMP
      if ($dt < 0) {
        continue;
      }

      // B: THROW ERROR
      #if ($dt<0) $this->_err('incorrect timestamp!');

      $time = $t;
      $dump .= Byte::writeVarLen($dt);

      // repetition, same event, same channel, omit first byte (smaller file size)
      $str = $this->dumpEvent($event);
      $start = ord($str[0]);
      if ($start >= 0x80 && $start <= 0xEF && $start == $last) {
        $str = substr($str, 1);
      }
      $last = $start;

      $dump .= $str;
    }
    $trackLen = strlen($dump) - $trackStart;
    return substr($dump, 0, $trackStart) . Byte::getBytes($trackLen, 4) . substr($dump, $trackStart);
  }

  /**
   * Returns the binary SMF code format for the specified MIDI event.
   */
  protected function dumpEvent(Event $event) {
    $binary = chr($event->getStatus());
    foreach ($event->getDataAsArray() as $data) {
      $binary .= chr($data);
    }
    return $binary;
  }

  protected function __getBinaryCode(Event $event) {
    switch ($event->getMnemonic()) {
      case 'PrCh': // 0x0C
        return chr($event->getStatus()) . chr($event->getData('p'));

      case 'On': // 0x09
        return chr($event->getStatus()) . chr($event->getData('n')) . chr($event->getData('v'));

      case 'Off': // 0x08
        return chr($event->getStatus()) . chr($event->getData('n')) . chr($event->getData('v'));

      case 'PoPr': // 0x0A = PolyPressure
        return chr($event->getStatus()) . chr($event->getData('n')) . chr($event->getData('v'));

      case 'Par': // 0x0B = ControllerChange
        return chr($event->getStatus()) . chr($event->getData('c')) . chr($event->getData('v'));

      case 'ChPr': // 0x0D = ChannelPressure
        return chr($event->getStatus()) . chr($event->getData('v'));

      case 'Pb': // 0x0E = PitchBend
        $value = $event->getData('v');
        $a = $value & 0x7f; // Bits 0..6
        $b = ($value >> 7) & 0x7f; // Bits 7..13
        return chr($event->getStatus()) . chr($a) . chr($b);

      // META EVENTS
      case 'Seqnr': // 0x00 = sequence_number
        $num = chr($msg[2]);
        if ($msg[2] > 255) {
          $this->_err("code broken around Seqnr event");
        }
        return "\xFF\x00\x02\x00$num";
        break;
      case 'Meta':
        $type = $msg[2];
        switch ($type) {
          case 'Text': //0x01: // Meta Text
          case 'Copyright': //0x02: // Meta Copyright
          case 'TrkName': //0x03: // Meta TrackName ???SeqName???
          case 'InstrName': //0x04: // Meta InstrumentName
          case 'Lyric': //0x05: // Meta Lyrics
          case 'Marker': //0x06: // Meta Marker
          case 'Cue': //0x07: // Meta Cue
            $texttypes = array(
              'Text',
              'Copyright',
              'TrkName',
              'InstrName',
              'Lyric',
              'Marker',
              'Cue'
            );
            $byte = chr(array_search($type, $texttypes) + 1);
            $start = strpos($line, '"') + 1;
            $end = strrpos($line, '"');
            $txt = substr($line, $start, $end - $start);
            $len = $this->_writeVarLen(strlen($txt)); // NEW
            return "\xFF$byte$len$txt";
            break;
          case 'TrkEnd': //0x2F
            return "\xFF\x2F\x00";
            break;
          case '0x20': // 0x20 = ChannelPrefix
            $v = chr($msg[3]);
            return "\xFF\x20\x01$v";
            break;
          case '0x21': // 0x21 = ChannelPrefixOrPort
            $v = chr($msg[3]);
            return "\xFF\x21\x01$v";
            break;
          default:
            $this->_err("unknown meta event: $type");
            exit();
        }
        break;
      case 'Tempo': // 0x51
        $tempo = $this->_getBytes((int) $msg[2], 3);
        return "\xFF\x51\x03$tempo";
        break;
      case 'SMPTE': // 0x54 = SMPTE offset
        $h = chr($msg[2]);
        $m = chr($msg[3]);
        $s = chr($msg[4]);
        $f = chr($msg[5]);
        $fh = chr($msg[6]);
        return "\xFF\x54\x05$h$m$s$f$fh";
        break;
      case 'TimeSig': // 0x58
        $zt = explode('/', $msg[2]);
        $z = chr($zt[0]);
        $t = chr(log($zt[1]) / log(2));
        $mc = chr($msg[3]);
        $c = chr($msg[4]);
        return "\xFF\x58\x04$z$t$mc$c";
        break;
      case 'KeySig': // 0x59
        $vz = chr($msg[2]);
        $g = chr(($msg[3] == 'major') ? 0 : 1);
        return "\xFF\x59\x02$vz$g";
        break;
      case 'SeqSpec': // 0x7F = Sequencer specific data (Bs: 0 SeqSpec 00 00 41)
        $cnt = count($msg) - 2;
        $data = '';
        for ($i = 0; $i < $cnt; $i++) {
          $data .= $this->_hex2bin($msg[$i + 2]);
        }
        $len = $this->_writeVarLen(strlen($data)); // NEW
        return "\xFF\x7F$len$data";
        break;
      case 'SysEx': // 0xF0 = SysEx
        $start = strpos($line, 'f0');
        $end = strrpos($line, 'f7');
        $data = substr($line, $start + 3, $end - $start - 1);
        $data = $this->_hex2bin(str_replace(' ', '', $data));
        $len = chr(strlen($data));
        return "\xF0$len" . $data;
        break;

      default:
        @$this->_err('unknown event: ' . $msg[1]);
        exit();
    }
  }

}
