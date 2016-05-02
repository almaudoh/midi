<?php

namespace KodeHauz\Midi;

class TextParser implements Parser {

  public function parse($string, $track) {
    $txt = trim($txt);
    // make unix text format
    if (strpos($txt, "\r") !== FALSE && strpos($txt, "\n") === FALSE) // MAC
    {
      $txt = str_replace("\r", "\n", $txt);
    }
    else // PC?
    {
      $txt = str_replace("\r", '', $txt);
    }
    $txt = $txt . "\n";// makes things easier

    $headerStr = strtok($txt, "\n");
    $header = explode(' ', $headerStr); //"MFile $type $tc $timebase";
    $this->type = $header[1];
    $this->timebase = $header[3];
    $this->tempo = 0;

    $trackStrings = explode("MTrk\n", $txt);
    array_shift($trackStrings);
    $tracks = array();
    foreach ($trackStrings as $trackStr) {
      $track = explode("\n", $trackStr);
      array_pop($track);
      array_pop($track);

      if ($track[0] == "TimestampType=Delta") {//delta
        array_shift($track);
        $track = $this->delta2Absolute($track);
      }

      $tracks[] = $track;
    }
    $this->tracks = $tracks;
    $this->findTempo();
  }

  /** imports track as text (mf2t-format) */
  public function importTrackTxt($txt, $tn) {
    $txt = trim($txt);
    // make unix text format
    if (strpos($txt, "\r") !== FALSE && strpos($txt, "\n") === FALSE) // MAC
    {
      $txt = str_replace("\r", "\n", $txt);
    }
    else // maybe PC, 0D 0A?
    {
      $txt = str_replace("\r", '', $txt);
    }

    $track = explode("\n", $txt);

    if ($track[0] == 'MTrk') {
      array_shift($track);
    }
    if ($track[count($track) - 1] == 'TrkEnd') {
      array_pop($track);
    }

    if ($track[0] == "TimestampType=Delta") {//delta
      array_shift($track);
      $track = $this->delta2Absolute($track);
    }

    $tn = isset($tn) ? $tn : count($this . tracks);
    $this->tracks[$tn] = $track;
    if ($tn == 0) {
      $this->_findTempo();
    }
  }

  /** search track 0 for set tempo msg */
  protected function findTempo() {
    $track = $this->tracks[0];
    $mc = count($track);
    for ($i = 0; $i < $mc; $i++) {
      $msg = explode(' ', $track[$i]);
      if ((int) $msg[0] > 0) {
        break;
      }
      if ($msg[1] == 'Tempo') {
        $this->tempo = $msg[2];
        $this->tempoMsgNum = $i;
        break;
      }
    }
  }

//***************************************************************
// UTILITIES
//***************************************************************

  /** converts all delta times in track to absolute times */
  protected function delta2Absolute($track) {
    $mc = count($track);
    $last = 0;
    for ($i = 0; $i < $mc; $i++) {
      $msg = explode(' ', $track[$i]);
      $t = $last + (int) $msg[0];
      $msg[0] = $t;
      $track[$i] = implode(' ', $msg);
      $last = $t;
    }
    return $track;
  }

}