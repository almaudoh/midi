<?php

namespace KodeHauz\Midi;

class XmlDumper implements Dumper {

  public function dump(Midi $midi, $ttype) {
    $tracks = $midi->getTracks();
    $tc = count($tracks);
    $type = ($tc > 1) ? 1 : 0;
    $timebase = $midi->getTimebase();
    $delta_or_abs = ($ttype == 1 ? 'Delta' : 'Absolute');

    $xml = <<<EOF
<?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE MIDIFile PUBLIC
  "-//Recordare//DTD MusicXML 0.9 MIDI//EN"
  "http://www.musicxml.org/dtds/midixml.dtd">
<MIDIFile>
<Format>$type</Format>
<TrackCount>$tc</TrackCount>
<TicksPerBeat>$timebase</TicksPerBeat>
<TimestampType>$delta_or_abs</TimestampType>
EOF;

    for ($i = 0; $i < $tc; $i++) {
      $xml .= "<Track Number=\"$i\">\n";
      $track = $tracks[$i];
      $mc = count($track);
      $last = 0;
      for ($j = 0; $j < $mc; $j++) {
        $msg = explode(' ', $track[$j]);
        $t = (int) $msg[0];
        if ($ttype == 1) {//delta
          $dt = $t - $last;
          $last = $t;
        }
        $xml .= "  <Event>\n";
        $xml .= ($ttype == 1) ? "    <Delta>$dt</Delta>\n" : "    <Absolute>$t</Absolute>\n";
        $xml .= '    ';

        switch ($msg[1]) {
          case 'PrCh':
            eval("\$" . $msg[2] . ';'); // $ch
            eval("\$" . $msg[3] . ';'); // $p
            $xml .= "<ProgramChange Channel=\"$ch\" Number=\"$p\"/>\n";
            break;

          case 'On':
          case 'Off':
            eval("\$" . $msg[2] . ';'); // $ch
            eval("\$" . $msg[3] . ';'); // $n
            eval("\$" . $msg[4] . ';'); // $v
            $xml .= "<Note{$msg[1]} Channel=\"$ch\" Note=\"$n\" Velocity=\"$v\"/>\n";
            break;

          case 'PoPr':
            eval("\$" . $msg[2] . ';'); // $ch
            eval("\$" . $msg[3] . ';'); // $n
            eval("\$" . $msg[4] . ';'); // $v
            $xml .= "<PolyKeyPressure Channel=\"$ch\" Note=\"$n\" Pressure=\"$v\"/>\n";
            break;

          case 'Par':
            eval("\$" . $msg[2] . ';'); // ch
            eval("\$" . $msg[3] . ';'); // c
            eval("\$" . $msg[4] . ';'); // v
            $xml .= "<ControlChange Channel=\"$ch\" Control=\"$c\" Value=\"$v\"/>\n";
            break;

          case 'ChPr':
            eval("\$" . $msg[2] . ';'); // ch
            eval("\$" . $msg[3] . ';'); // v
            $xml .= "<ChannelKeyPressure Channel=\"$ch\" Pressure=\"$v\"/>\n";
            break;

          case 'Pb':
            eval("\$" . $msg[2] . ';'); // ch
            eval("\$" . $msg[3] . ';'); // v
            $xml .= "<PitchBendChange Channel=\"$ch\" Value=\"$v\"/>\n";
            break;

          case 'Seqnr':
            $xml .= "<SequenceNumber Value=\"{$msg[2]}\"/>\n";
            break;

          case 'Meta':
            $txttypes = array(
              'Text',
              'Copyright',
              'TrkName',
              'InstrName',
              'Lyric',
              'Marker',
              'Cue'
            );
            $mtype = $msg[2];

            $pos = array_search($mtype, $txttypes);
            if ($pos !== FALSE) {
              $tags = array(
                'TextEvent',
                'CopyrightNotice',
                'TrackName',
                'InstrumentName',
                'Lyric',
                'Marker',
                'CuePoint'
              );
              $tag = $tags[$pos];
              $line = $track[$j];
              $start = strpos($line, '"') + 1;
              $end = strrpos($line, '"');
              $txt = substr($line, $start, $end - $start);
              $xml .= "<$tag>" . htmlspecialchars($txt) . "</$tag>\n";
            }
            else {
              if ($mtype == 'TrkEnd') {
                $xml .= "<EndOfTrack/>\n";
              }
              elseif ($mtype == '0x20' || $mtype == '0x21') // ChannelPrefix ???
              {
                $xml .= "<MIDIChannelPrefix Value=\"{$msg[3]}\"/>\n";
              }
            }
            break;

          case 'Tempo':
            $xml .= "<SetTempo Value=\"{$msg[2]}\"/>\n";
            break;

          case 'SMPTE':
            $xml .= "<SMPTEOffset TimeCodeType=\"1\" Hour=\"{$msg[2]}\" Minute=\"{$msg[3]}\" Second=\"{$msg[4]}\" Frame=\"{$msg[5]}\" FractionalFrame=\"{$msg[6]}\"/>\n"; //TimeCodeType???
            break;

          case 'TimeSig': // LogDenum???
            $ts = explode('/', $msg[2]);
            $xml .= "<TimeSignature Numerator=\"{$ts[0]}\" LogDenominator=\"" . log($ts[1]) / log(2) . "\" MIDIClocksPerMetronomeClick=\"{$msg[3]}\" ThirtySecondsPer24Clocks=\"{$msg[4]}\"/>\n";
            break;

          case 'KeySig':
            $mode = ($msg[3] == 'major') ? 0 : 1;
            $xml .= "<KeySignature Fifths=\"{$msg[2]}\" Mode=\"$mode\"/>\n"; // ???
            break;

          case 'SeqSpec':
            $line = $track[$j];
            $start = strpos($line, 'SeqSpec') + 8;
            $data = substr($line, $start);
            $xml .= "<SequencerSpecific>$data</SequencerSpecific>\n";
            break;

          case 'SysEx':
            $str = '';
            for ($k = 3; $k < count($msg); $k++) {
              $str .= $msg[$k] . ' ';
            }
            $str = trim(strtoupper($str));
            $xml .= "<SystemExclusive>$str</SystemExclusive>\n";
            break;
          /* TODO:
          <AllSoundOff Channel="9"/>
          <ResetAllControllers Channel="9"/>
          <LocalControl Channel="9" Value="on"/>
          <AllNotesOff Channel="9"/>
          <OmniOff Channel="9"/>
          <OmniOn Channel="9"/>
          <MonoMode Channel="9" Value="5"/>
          <PolyMode Channel="9"/>
          */
          default:
            $this->err('unknown event: ' . $msg[1]);
            exit();
        }
        $xml .= "  </Event>\n";
      }
      $xml .= "</Track>\n";
    }
    $xml .= "</MIDIFile>";
    return $xml;
  }
}
