<?php

namespace KodeHauz\Midi\Event;

use KodeHauz\Midi\Event\Meta\Tempo;

class Track {

  /**
   * The MIDI events that make up this track.
   *
   * @var \KodeHauz\Midi\Event\Event[]
   */
  protected $events = array();

  /**
   * Creates a new MIDI track.
   *
   * @param \KodeHauz\Midi\Event\Event[] $midi_events
   *   The MIDI events that combine to make up this track.
   */
  public function __construct(array $midi_events = array()) {
    $this->events = $midi_events;
  }

  public function setTempo($tempo) {
    // @todo: Optimize this. Check that there is no tempo event already.
    $tempo_event = new Tempo(0, $tempo);
    array_unshift($this->events, $tempo_event);
  }

  public function getTempo() {
    $tempo_events = $this->findEventsOfType(Tempo::class);
  }

  /**
   * Gets the track $tn as array of msg strings
   *
   */
  public function getAsStringArray() {
    $tracks = array();
    foreach ($this->events as $event) {
      $tracks[] = (string) $event;
    }
    return $tracks;
  }

  /**
   * Gets the number of MIDI messages in this track.
   *
   * @return int
   *   The track count.
   */
  public function getSize() {
    return count($this->events);
  }

  /**
   * Adds a MIDI event at the end of the track.
   *
   * @param \KodeHauz\Midi\Event\Event $event
   *   The MIDI event to be added.
   * @param int $time_type
   *   The event timing type:
   *   - absolute time: 0
   *   - delta time: 1
   */
  public function addEvent(Event $event, $time_type = 0) {
    if ($time_type == 1) {
      $last = $this->events[count($this->events) - 1]->getTime();
      $event->setTime($last + $event->getTime());
    }
    $this->events[] = $event;
  }

  /**
   * Inserts a MIDI event at the specified position in the track.
   *
   * This method is slower than ::addEvent.
   *
   * @param \KodeHauz\Midi\Event\Event $event
   *   The MIDI event to be added.
   */
  public function insertEvent(Event $event) {
    $time = $event->getTime();
    $event_count = count($this->events);
    for ($i = 0; $i < $event_count; $i++) {
      if ($this->events[$i]->getTime() >= $time) {
        break;
      }
    }
    array_splice($this->events, $i, 0, $event);
  }

  /**
   * Gets the MIDI event specified by $number.
   *
   * @param int $number
   *   The MIDI event sequence number.
   *
   * @return \KodeHauz\Midi\Event\Event
   */
  public function getEvent($number) {
    return $this->events[$number];
  }

  /**
   * Deletes the MIDI event specified by $number.
   *
   * @param $number
   *   The MIDI event sequence number.
   */
  public function deleteEvent($number) {
    array_splice($this->events, $number, 1);
  }

  /**
   * Transposes the track by specified number of half-tone steps
   *
   * The transposition is always downwards.
   *
   * @param int $half_tones
   *   The number of half-tone steps to transpose the track.
   *
   * @todo Check the formulas and definition of "$half_tone".
   */
  public function transpose($half_tones) {
    foreach ($this->events as $event) {
      if ($event instanceof OnMessage || $event instanceof OffMessage) {
        $note = max(0, min(127, $event->getData('n') + $half_tones));
        $event->setData('n', $note);
//        $this->events[$i] = join(' ', $msg);
      }
    }
  }

  /**
   * Gets all the MIDI events in this track.
   *
   * @return \KodeHauz\Midi\Event\Event[]
   */
  public function getAllEvents() {
    return $this->events;
  }

  public function findEventsOfType($event_type) {
    return array_filter($this->events, function($event) use ($event_type) {
      return ($event instanceof $event_type);
    });
  }

  public function __toString() {
    return implode("\n", $this->getAsStringArray());
  }

}
