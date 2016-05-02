<?php

/****************************************************************************
 * Software: Midi Class
 * Version:  1.7.8
 * Date:     2013-11-02
 * Author:   Valentin Schmidt
 * License:  Freeware
 *
 * You may use and modify this software as you wish.
 *
 * Last Changes:
 * - added variable length encoding to Meta and SeqSpec Events
 ****************************************************************************/

namespace KodeHauz\Midi;

use KodeHauz\Midi\Event\Meta\Tempo;
use KodeHauz\Midi\Event\Meta\TimeSignature;
use KodeHauz\Midi\Event\Meta\TrackEnd;
use KodeHauz\Midi\Event\Track;

class Midi {

  /**
   * Array of tracks, where each track is an array of message strings.
   *
   * @var \KodeHauz\Midi\Event\Track[]
   */
  protected $tracks;

  /**
   * Timebase = ticks per frame (quarter note).
   *
   * @var integer
   */
  protected $timebase;

  /**
   * Tempo as integer (0 for unknown).
   *
   * @var integer
   */
  protected $tempo;

  /**
   * Position of tempo event in track 0.
   *
   * @var integer
   */
  protected $tempoMsgNum;

  /**
   * SMF type 0 or 1 (0=only a single track).
   *
   * @var integer
   */
  protected $type;

  /**
   * Whether to throw exception on error (only PHP5+).
   *
   * @var bool
   */
  protected $throwFlag;

  /**
   *
   * Constructs a new Midi class.
   */
  public function __construct() {
    $this->tracks = array();
    $this->throwFlag = ((int) phpversion() >= 5);
  }

  /**
   * Creates (or resets to) new empty MIDI song.
   *
   * @param int $timebase
   */
  public function open($timebase = 480) {
    $this->tempo = 0; // 125000 = 120 bpm.
    $this->timebase = $timebase;
    $this->tracks = array();
  }

  /**
   * Sets tempo by replacing set tempo msg in track 0 (or adding new track 0).
   */
  public function setTempo($tempo) {
    $tempo = round($tempo);
    if (isset($this->tempoMsgNum)) {
      $this->tracks[0]->setTempo($tempo);
    }
    else {
      $tempoTrack = array(
        new TimeSignature(0, 4, 4, 24, 8),
        new Tempo(0, $tempo),
        new TrackEnd(0),
      );
      array_unshift($this->tracks, $tempoTrack);
      $this->tempoMsgNum = 1;
    }
    $this->tempo = $tempo;
  }

  /**
   * Gets the tempo.
   *
   * @return int
   *   The tempo (0 if not set).
   */
  public function getTempo() {
    // @todo: Should we read from track 0 directly?
    return $this->tempo;
  }

  /**
   * Sets tempo corresponding to given bpm.
   *
   * @param int $bpm
   *   The tempo in beats per minute (BPM).
   */
  public function setBpm($bpm) {
    $tempo = round(60000000 / $bpm);
    $this->setTempo($tempo);
  }

  /**
   * Returns bpm corresponding to tempo.
   *
   * @return int
   *   The tempo (0 if not set).
   */
  public function getBpm() {
    return ($this->tempo != 0) ? (int) (60000000 / $this->tempo) : 0;
  }

  /**
   * Sets the timebase.
   * 
   * @param int $tb
   *   The timebase.
   */
  public function setTimebase($tb) {
    $this->timebase = $tb;
  }

  /**
   * Gets the timebase.
   *
   * @return int
   *   The timebase.
   */
  public function getTimebase() {
    return $this->timebase;
  }

  /**
   * Adds a new track to the MIDI file, returning back the new tracks count.
   *
   * @param \KodeHauz\Midi\Event\Track $track
   *   (optional) The track to be added. If nothing is passed, a blank new track
   *   will be added.
   *
   * @return int
   *   The track count.
   */
  public function addTrack($track = NULL) {
    if (!isset($track)) {
      $track = new Track();
    }
    array_push($this->tracks, $track);
    return count($this->tracks);
  }

  /**
   * Gets the track specified by track number
   *
   * @param int $track_number
   *   The track number.
   *
   * @return \KodeHauz\Midi\Event\Track
   */
  public function getTrack($track_number) {
    return $this->tracks[$track_number];
  }

  /**
   * Gets all the tracks contained in this MIDI file.
   *
   * @return \KodeHauz\Midi\Event\Track[]
   */
  public function getTracks() {
    return $this->tracks;
  }

  /**
   * Deletes the specified track from the MIDI file.
   *
   * @parem int $track_number
   *   The track number to be deleted.
   */
  public function deleteTrack($track_number) {
    array_splice($this->tracks, $track_number, 1);
    return count($this->tracks);
  }

  /**
   * Gets the number of tracks in the MIDI file.
   *
   * @return int
   */
  public function getTrackCount() {
    return count($this->tracks);
  }

  /**
   * Deletes all the tracks except the specified track.
   *
   * This also retains track 0 which contains tempo and other meta info.
   *
   * @param int $track_number
   *   The track number to be retained.
   */
  public function soloTrack($track_number) {
    if ($track_number == 0) {
      $this->tracks = array($this->tracks[0]);
    }
    else {
      $this->tracks = array($this->tracks[0], $this->tracks[$track_number]);
    }
  }

  /**
   * Transposes a song by the specified number of half tone-steps.
   *
   * The transposition is going to be downwards.
   *
   * @param int $half_tones
   *   The number of half-tone steps to transpose.
   */
  public function transpose($half_tones) {
    for ($i = 0; $i < count($this->tracks); $i++) {
      $this->tracks[$i]->transpose($half_tones);
    }
  }

  /** import whole MIDI song as text (mf2t-format) */
  public function importTxt($txt) {
    $parser = new TextParser($this);
    $this->setTempo(0);
    $this->tracks = $parser->parse($txt);
  }

  /** returns MIDI song as text */
  public function getTxt($ttype = 0) { //0:absolute, 1:delta
    $dumper = new TextDumper();
    return $dumper->dump($this, $ttype);
  }

//---------------------------------------------------------------
// import MIDI XML representation
// (so far only a subset of http://www.musicxml.org/dtds/midixml.dtd (v0.8), see documentation)
//---------------------------------------------------------------
  public function importXml($xmlStr) {
    $this->evt = array();
    $this->atr = array();
    $this->dat = '';
    $this->open();

    $parser = new XmlParser($this);
    $this->tracks = $parser->parse($xmlStr);
  }

  /** returns MIDI XML representation (v0.9, http://www.musicxml.org/dtds/midixml.dtd) */
  public function getXml($ttype = 0) { //0:absolute, 1:delta
    $dumper = new XmlDumper();
    return $dumper->dump($this, $ttype);
  }



//---------------------------------------------------------------
// imports Standard MIDI File (typ 0 or 1) (and RMID)
// (if optional parameter $tn set, only track $tn is imported)
//---------------------------------------------------------------
  public function importMid($smf_path, $tn = NULL) {
    $SMF = fopen($smf_path, "rb"); // Standard MIDI File, typ 0 or 1
    $song = fread($SMF, filesize($smf_path));
    fclose($SMF);

    $parser = new MidiParser($this);
    // maybe (hopefully!) overwritten by MidiParser::parseTrack()
    $this->setTempo(0);
    $this->tracks = $parser->parse($song, $this->timebase, $tn);
  }

  /** returns binary MIDI string */
  public function getMid() {
    $dumper = new MidiDumper();
    return $dumper->dump($this, 0);
  }

  /** saves MIDI song as Standard MIDI File */
  public function saveMidFile($mid_path, $chmod = FALSE) {
    if (count($this->tracks) < 1) {
      throw new MidiException('MIDI song has no tracks');
    }
    $SMF = fopen($mid_path, "wb"); // SMF
    fwrite($SMF, $this->getMid());
    fclose($SMF);
    if ($chmod !== FALSE) {
      @chmod($mid_path, $chmod);
    }
  }

  /** embeds Standard MIDI File (according to template) */
  public function playMidFile($file, $visible = TRUE, $autostart = TRUE, $loop = TRUE, $player = 'default') {
    include('player/' . $player . '.tpl.php');
  }

//---------------------------------------------------------------
// starts download of Standard MIDI File, either from memory or from the server's filesystem
// ATTENTION: order of params swapped, so $file can be omitted to directly start download
//---------------------------------------------------------------
  public function downloadMidFile($output, $file = FALSE) {
    ob_start("ob_gzhandler"); // for compressed output...

    //$mime_type = 'audio/midi';
    $mime_type = 'application/octetstream'; // force download

    header('Content-Type: ' . $mime_type);
    header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Content-Disposition: attachment; filename="' . $output . '"');
    header('Pragma: no-cache');

    if ($file) {
      $d = fopen($file, "rb");
      fpassthru($d);
      @fclose($d);
    }
    else {
      echo $this->getMid();
    }
    exit();
  }

}
