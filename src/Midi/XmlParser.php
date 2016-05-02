<?php

namespace KodeHauz\Midi;

class XmlParser implements Parser {

  /**
   * An XML parser resource.
   *
   * @var resource
   */
  protected $xml_parser;

  /**
   * Creates a new XmlParser object.
   *
   * @param string $xmlStr
   */
  public function __construct($xmlStr) {

    $this->xml_parser = xml_parser_create("ISO-8859-1");
    xml_set_object($this->xml_parser, $this);
    xml_set_element_handler($this->xml_parser, "startElement", "endElement");
    xml_set_character_data_handler($this->xml_parser, "chData");
    if (!xml_parse($this->xml_parser, $xmlStr, TRUE)) {
      die(sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($this->xml_parser)), xml_get_current_line_number($this->xml_parser)));
    }
    xml_parser_free($this->xml_parser);

  }

  public function parse($string) {

  }

  /** XML PARSING CALLBACKS */
  protected function startElement($parser, $name, $attrs) {

    switch ($name) {
      case 'MIDIFILE':
      case 'FORMAT':
      case 'TRACKCOUNT':
      case 'TICKSPERBEAT':
      case 'TIMESTAMPTYPE':
      case 'DELTA':
      case 'ABSOLUTE':
        break;
      case 'TRACK':
        $this->newTrack();
        if ($this->ttype == 'Delta') {
          $this->t = 0;
        }
        break;

      case 'EVENT':
        $this->evt = array();
        $this->atr = array();
        $this->dat = '';
        break;

      default:
        $this->atr = $attrs;
    }
  }

  protected function endElement($parser, $name) {
    switch ($name) {
      case 'MIDIFILE':
        break;
      case 'FORMAT':
        $this->type = (int) $this->dat;
        break;
      case 'TRACKCOUNT':
        break;
      case 'TICKSPERBEAT':
        $this->timebase = (int) $this->dat;
        break;
      case 'TIMESTAMPTYPE':
        $this->ttype = $this->dat;//DELTA or ABSOLUTE
        break;
      case 'TRACK':
        break;

      case 'DELTA':
        $this->t = $this->t + (int) $this->dat;
        $this->evt['t'] = $this->t;
        break;
      case 'ABSOLUTE':
        $this->evt['t'] = (int) $this->dat;
        break;

      case 'EVENT':
        $time = $this->evt['t'];
        $a = $this->evt['atr'];
        $typ = $this->evt['typ'];
        $dat = $this->evt['dat'];
        $tn = count($this->tracks) - 1;

        switch ($typ) {
          case 'PROGRAMCHANGE':
            $msg = "$time PrCh ch={$a['CHANNEL']} p={$a['NUMBER']}";
            break;
          case 'NOTEON':
            $msg = "$time On ch={$a['CHANNEL']} n={$a['NOTE']} v={$a['VELOCITY']}";
            break;
          case 'NOTEOFF':
            $msg = "$time Off ch={$a['CHANNEL']} n={$a['NOTE']} v={$a['VELOCITY']}";
            break;
          case 'POLYKEYPRESSURE':
            $msg = "$time PoPr ch={$a['CHANNEL']} n={$a['NOTE']} v={$a['PRESSURE']}";
            break;
          case 'CONTROLCHANGE':
            $msg = "$time Par ch={$a['CHANNEL']} c={$a['CONTROL']} v={$a['VALUE']}";
            break;
          case 'CHANNELKEYPRESSURE':
            $msg = "$time ChPr ch={$a['CHANNEL']} v={$a['PRESSURE']}";
            break;
          case 'PITCHBENDCHANGE':
            $msg = "$time Pb ch={$a['CHANNEL']} v={$a['VALUE']}";
            break;

          case 'SEQUENCENUMBER':
            $msg = "$time Seqnr {$a['VALUE']}";
            break;

          case 'TEXTEVENT':
          case 'COPYRIGHTNOTICE':
          case 'TRACKNAME':
          case 'INSTRUMENTNAME':
          case 'LYRIC':
          case 'MARKER':
          case 'CUEPOINT':
            $tags = array(
              'TEXTEVENT',
              'COPYRIGHTNOTICE',
              'TRACKNAME',
              'INSTRUMENTNAME',
              'LYRIC',
              'MARKER',
              'CUEPOINT'
            );
            $txttypes = array(
              'Text',
              'Copyright',
              'TrkName',
              'InstrName',
              'Lyric',
              'Marker',
              'Cue'
            );
            $type = $txttypes[array_search($typ, $tags)];
            $msg = "$time Meta $type \"$dat\"";
            break;

          case 'ENDOFTRACK':
            $msg = "$time Meta TrkEnd";
            break;

          case 'MIDICHANNELPREFIX'://???
            if ((int) $dat < 10) {
              $dat = '0' . $dat;
            }
            $msg = "$time Meta 0x20 $dat";
            break;

          case 'SETTEMPO':
            $tempo = (int) $a['VALUE'];
            $msg = "$time Tempo $tempo";
            if ($tn == 0 && $time == 0) {//???
              $this->tempo = $tempo;
              $this->tempoMsgNum = count($this->tracks[$tn]);//???
            }
            break;

          case 'SMPTEOFFSET'://???
            $msg = "$time SMPTE {$a['HOUR']} {$a['MINUTE']} {$a['SECOND']} {$a['FRAME']} {$a['FRACTIONALFRAME']}";
            break;

          case 'TIMESIGNATURE':
            $z = (int) $a['NUMERATOR'];
            $t = pow(2, (int) $a['LOGDENOMINATOR']);
            $msg = "$time TimeSig $z/$t {$a['MIDICLOCKSPERMETRONOMECLICK']} {$a['THIRTYSECONDSPER24CLOCKS']}";
            break;

          case 'KEYSIGNATURE':
            $g = ($a['MODE'] == 0) ? 'major' : 'minor';
            $msg = "$time KeySig {$a['FIFTHS']} $g";
            break;

          case 'SEQUENCERSPECIFIC':
            $msg = "$time SeqSpec $dat";
            break;

          case 'SYSTEMEXCLUSIVE'://???
            $dat = strtolower($dat);
            $msg = "$time SysEx f0 $dat";
            break;

          default:
            return;//ignore
        }

        $this->addMsg(count($this->tracks) - 1, $msg);
        $evt = 0;
        break;

      default://within event!?
        $this->evt['typ'] = $name;
        $this->evt['atr'] = $this->atr;
        $this->evt['dat'] = $this->dat;
    }
  }

  protected function chData($parser, $data) {
    $this->dat = (trim($data) == '') ? '' : $data;//???
  }

}
