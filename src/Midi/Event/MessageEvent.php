<?php

namespace KodeHauz\Midi\Event;

/**
 * Encapsulates a MIDI message event.
 */
abstract class MessageEvent implements Event {

  protected $time;
  protected $status;
  protected $data;
  protected $mnemonic;

  protected function __construct($time, $high_byte, $channel, $data, $mnemonic) {
    if ($high_byte > 127) {
      throw new \InvalidArgumentException('High byte value cannot exceed 127');
    }
    if ($channel < 1 || $channel > 16) {
      throw new \InvalidArgumentException('Channel must be between 1 and 16');
    }
    $this->time = $time;
    $this->status = ($high_byte << 4) | ($channel - 1);
    $this->data = $data;
    $this->mnemonic = $mnemonic;
  }

  public function __toString() {
    $channel = (0x0F & $this->status) + 1;
    $text = "{$this->time} {$this->mnemonic} ch=$channel ";
    foreach ($this->data as $key => $value) {
      $text .= "$key=$value ";
    }
    return rtrim($text);
  }

  /**
   * {@inheritdoc}
   */
  public function getTime() {
    return $this->time;
  }

  /**
   * {@inheritdoc}
   */
  public function setTime($time) {
    $this->time = $time;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getData($index) {
    return $this->data[$index];
  }

  /**
   * {@inheritdoc}
   */
  public function setData($index, $value) {
    $this->data[$index] = $value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMnemonic() {
    return $this->mnemonic;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * {@inheritdoc}
   */
  public function getDataAsArray() {
    return $this->data;
  }

}
