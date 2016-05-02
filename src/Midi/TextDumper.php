<?php

namespace KodeHauz\Midi;

use KodeHauz\Midi\Event\Track;

class TextDumper implements Dumper {

  public function dump(Midi $midi, $ttype) {
    $timebase = $midi->getTimebase();
    $tc = $midi->getTrackCount();
    $type = ($tc > 1) ? 1 : 0;
    $str = "MFile $type $tc $timebase\n";
    foreach ($midi->getTracks() as $i => $track) {
      $str .= $this->getTrackTxt($track, $ttype);
    }
    return $str;
  }

  /** returns track as text */
  public function getTrackTxt(Track $track, $ttype = 0) { //0:absolute, 1:delta
    $str = "MTrk\n";
    if ($ttype == 1) { //time as delta
      $str .= "TimestampType=Delta\n";
      $last = 0;
      foreach ($track->getAllEvents() as $event) {
        $time = $event->getTime();
        $event->setTime($time - $last);
        $str .= $event . "\n";
        $last = $time;
      }
    }
    else {
      foreach ($track as $event) {
        $str .= $event . "\n";
      }
    }
    $str .= "TrkEnd\n";
    return $str;
  }

}
