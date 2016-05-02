<?php

namespace KodeHauz\Midi\Event;

interface Event {

  /**
   * Ensure events have a string format implemented.
   *
   * @return string
   */
  public function __toString();

  /**
   * Gets the absolute time when the MIDI event occurs.
   *
   * @return int
   */
  public function getTime();

  /**
   * Sets the absolute time when the MIDI event occurs.
   *
   * @param int $time
   *   The absolute time.
   *
   * @return $this
   *   For method chaining.
   */
  public function setTime($time);

  /**
   * Gets a specified data index value.
   *
   * @param int $index
   *   The index for which data is to be looked-up.
   *
   * @return mixed
   */
  public function getData($index);

  /**
   * Sets a specified data index value.
   *
   * @param int $index
   *   The index value. Note: this value should already have been initialized in
   *   the constructor to maintain the right order for binary serialization.
   *
   * @param mixed $value
   *   The value to be set.
   *
   * @return $this
   */
  public function setData($index, $value);

  /**
   * Gets the mnemonic used to encode the MIDI event in other text formats.
   *
   * @return string
   */
  public function getMnemonic();

  /**
   * Gets the status byte for this MIDI event.
   *
   * @return int
   */
  public function getStatus();

  /**
   * Gets the entire data as an array.
   *
   * @return array
   */
  public function getDataAsArray();

  /**
   * Gets the l
   */

}
