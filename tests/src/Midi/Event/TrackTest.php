<?php

namespace KodeHauz\Tests\Midi\Event;

use KodeHauz\Midi\Event\ChannelPressureMessage;
use KodeHauz\Midi\Event\ControllerChangeMessage;
use KodeHauz\Midi\Event\Meta\Tempo;
use KodeHauz\Midi\Event\OffMessage;
use KodeHauz\Midi\Event\OnMessage;
use KodeHauz\Midi\Event\PolyPressureMessage;
use KodeHauz\Midi\Event\ProgramChangeMessage;
use KodeHauz\Midi\Event\Track;

/**
 * Tests various MIDI messages events.
 *
 * @group midi
 */
class TrackTest extends \PHPUnit_Framework_TestCase {

  public function testFindEventsOfType() {
    $track = new Track();
    $channel = 0;
    $track->addEvent(new Tempo(0, 100));
    $track->addEvent(new OnMessage(0, $channel, 220, 100));
    $track->addEvent(new OnMessage(20, $channel, 230, 100));
    $track->addEvent(new OnMessage(40, $channel, 240, 100));
  }

  public function testSetTempo() {
    $track = new Track();
    $track->setTempo(280);
    $this->assertEquals(280, $track->getTempo());
  }

  public function testOffMessage() {
    $message = new OffMessage(3, 2, 460, 120);

    $this->assertEquals((string) $message, '3 Off ch=2 n=460 v=120');
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

}
