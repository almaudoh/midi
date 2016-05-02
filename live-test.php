<?php

use KodeHauz\Midi\Event\Meta\Tempo;
use KodeHauz\Midi\Event\Meta\TimeSignature;
use KodeHauz\Midi\Event\Meta\TrackEnd;
use KodeHauz\Midi\Event\OffMessage;
use KodeHauz\Midi\Event\OnMessage;
use KodeHauz\Midi\Event\Track;
use KodeHauz\Midi\Midi;

require_once 'vendor/autoload.php';

$midi_string = loadSampleFile(__DIR__ . '/tests/sample_files/bossa.mid');

$parser = new \KodeHauz\Midi\MidiParser();
$parser->parse($midi_string);

function createAndEdit() {
  $midi = new Midi();
  $midi->addTrack(array(
    new TimeSignature(0, 4, 2, 850, 10),
    new Tempo(0, 100),
    new TrackEnd(0),
  ));
  $track = new Track();
  $track->addEvent(new OnMessage(0, 1, 235, 100));
  $last_note = 235;
  $last_vel = 100;
  for ($i = 1; $i < 100; $i++) {
    $note = 240 + rand(1, 100);
    $vel = 50;
    $time = rand(5, 50);
    $track->addEvent(new OffMessage($time, 1, $last_note, $last_vel));
    $track->addEvent(new OnMessage($time, 1, $note, $vel));
    $last_note = $note;
    $last_vel = $vel;
  }


  $midi->addTrack($track);

  echo $midi->getTxt();
//echo $midi->getMid();
}

function loadSampleFile($filename) {
  $file = fopen($filename, "rb"); // Standard MIDI File, typ 0 or 1
  $file_content = fread($file, filesize($filename));
  fclose($file);
  return $file_content;
}

