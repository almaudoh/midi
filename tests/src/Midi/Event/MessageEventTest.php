<?php

namespace KodeHauz\Tests\Midi\Event;

use KodeHauz\Midi\Event\ChannelPressureMessage;
use KodeHauz\Midi\Event\ControllerChangeMessage;
use KodeHauz\Midi\Event\OffMessage;
use KodeHauz\Midi\Event\OnMessage;
use KodeHauz\Midi\Event\PitchBendMessage;
use KodeHauz\Midi\Event\PolyPressureMessage;
use KodeHauz\Midi\Event\ProgramChangeMessage;

/**
 * Tests various MIDI messages events.
 *
 * @group midi
 */
class MessageEventTest extends \PHPUnit_Framework_TestCase {

  public function testOnMessage() {
    $message = new OnMessage(0, 1, 46, 100);

    $this->assertEquals((string) $message, '0 On ch=1 n=46 v=100');
  }

  public function testOffMessage() {
    $message = new OffMessage(3, 2, 60, 120);

    $this->assertEquals((string) $message, '3 Off ch=2 n=60 v=120');
  }

  public function testPolyPressureMessage() {
    $message = new PolyPressureMessage(20, 5, 125, 60);

    $this->assertEquals((string) $message, '20 PoPr ch=5 n=125 v=60');
  }

  public function testControllerChangeMessage() {
    $message = new ControllerChangeMessage(12, 3, 43, 127);

    $this->assertEquals((string) $message, '12 Par ch=3 c=43 v=127');
  }

  public function testProgramChangeMessage() {
    $message = new ProgramChangeMessage(28, 2, 80);

    $this->assertEquals((string) $message, '28 PrCh ch=2 p=80');
  }

  public function testChannelPressureMessage() {
    $message = new ChannelPressureMessage(3, 12, 49);

    $this->assertEquals((string) $message, '3 ChPr ch=12 v=49');
  }

  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage Note value must be positive and cannot exceed 127
   */
  public function testInvalidOnMessageValue() {
    $message = new OnMessage(0, 1, 150, 127);
  }

  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage Note velocity must be positive and cannot exceed 127
   */
  public function testInvalidOnMessageVelocity() {
    $message = new OnMessage(0, 1, 127, 200);
  }

  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage Channel must be between 1 and 16
   */
  public function testInvalidOnMessageChannel() {
    $message = new OnMessage(0, 0, 127, 127);
  }

  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage Note value must be positive and cannot exceed 127
   */
  public function testInvalidOffMessageValue() {
    $message = new OffMessage(0, 1, 150, 127);
  }

  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage Note velocity must be positive and cannot exceed 127
   */
  public function testInvalidOffMessageVelocity() {
    $message = new OffMessage(0, 1, 127, 200);
  }

  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage Channel must be between 1 and 16
   */
  public function testInvalidOffMessageChannel() {
    $message = new OffMessage(0, 0, 127, 127);
  }

  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage Channel must be between 1 and 16
   */
  public function testInvalidPolyPressureMessageChannel() {
    $message = new PolyPressureMessage(20, 25, 125, 60);
  }

  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage Pitch bend value must be positive and cannot exceed 16384
   */
  public function testInvalidPitchBendValue() {
    $message = new PitchBendMessage(0, 1, 32000);
  }

}
