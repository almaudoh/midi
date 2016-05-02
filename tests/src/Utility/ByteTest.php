<?php

namespace KodeHauz\Tests\Utility;

use KodeHauz\Utility\Byte;

/**
 * Tests the Byte helper class for parsing MIDI bytes.
 *
 * @group midi
 */
class ByteTest extends \PHPUnit_Framework_TestCase {

  /**
   * @dataProvider providerVarLen
   */
  public function testWriteVarLen($number, $var_len_value) {
    $this->assertEquals(Byte::writeVarLen($number), $var_len_value);
  }

  /**
   * @dataProvider providerVarLen
   */
  public function testReadVarLen($number, $var_len_value) {
    $pos = 0;
    $this->assertEquals(Byte::readVarLen($var_len_value, $pos), $number);
    $this->assertEquals($pos, strlen($var_len_value));
  }

  /**
   * @dataProvider providerVarLenArray
   */
  public function testGetVarLen($number, array $var_len_array) {
    $this->assertEquals(Byte::getVarLen($number), $var_len_array);
  }

  /**
   * @dataProvider providerHextoBinary
   */
  public function testHextoBinaryString($hex_string, $binary_string, $binary_array) {
    $this->assertEquals($binary_string, Byte::hexToBinaryString($hex_string));
  }

  /**
   * @dataProvider providerHextoBinary
   */
  public function testHextoBinaryArray($hex_string, $binary_string, $binary_array) {
    $this->assertEquals($binary_array, Byte::hexToBinaryArray($hex_string));
  }

  /**
   * @dataProvider providerStringToByteArray
   */
  public function testStringToByteArray($string, $byte_array) {
    $this->assertEquals($byte_array, Byte::stringToByteArray($string));
  }

  public function providerVarLen() {
    return array(
      array(0x00000000, chr(0x00)),
      array(0x00000040, chr(0x40)),
      array(0x0000007F, chr(0x7F)),
      array(0x00000080, chr(0x81) . chr(0x00)),
      array(0x00002000, chr(0xC0) . chr(0x00)),
      array(0x00003FFF, chr(0xFF) . chr(0x7F)),
      array(0x00004000, chr(0x81) . chr(0x80) . chr(0x00)),
      array(0x00100000, chr(0xC0) . chr(0x80) . chr(0x00)),
      array(0x001FFFFF, chr(0xFF) . chr(0xFF) . chr(0x7F)),
      array(0x00200000, chr(0x81) . chr(0x80) . chr(0x80) . chr(0x00)),
      array(0x08000000, chr(0xC0) . chr(0x80) . chr(0x80) . chr(0x00)),
      array(0x0FFFFFFF, chr(0xFF) . chr(0xFF) . chr(0xFF) . chr(0x7F)),
    );
  }

  public function providerVarLenArray() {
    return array(
      array(0x00000000, array(0x00)),
      array(0x00000040, array(0x40)),
      array(0x0000007F, array(0x7F)),
      array(0x00000080, array(0x81, 0x00)),
      array(0x00002000, array(0xC0, 0x00)),
      array(0x00003FFF, array(0xFF, 0x7F)),
      array(0x00004000, array(0x81, 0x80, 0x00)),
      array(0x00100000, array(0xC0, 0x80, 0x00)),
      array(0x001FFFFF, array(0xFF, 0xFF, 0x7F)),
      array(0x00200000, array(0x81, 0x80, 0x80, 0x00)),
      array(0x08000000, array(0xC0, 0x80, 0x80, 0x00)),
      array(0x0FFFFFFF, array(0xFF, 0xFF, 0xFF, 0x7F)),
    );
  }

  public function providerHexToBinary() {
    return array(
      array('000000', chr(0x00) . chr(0x00) . chr(0x00), array(0x00, 0x00, 0x00)),
      array('00FE22', chr(0x00) . chr(0xFE) . chr(0x22), array(0x00, 0xFE, 0x22)),
      array('CB0163', chr(0xCB) . chr(0x01) . chr(0x63), array(0xCB, 0x01, 0x63)),
      array('A2DC89', chr(0xA2) . chr(0xDC) . chr(0x89), array(0xA2, 0xDC, 0x89)),
      array('072A10', chr(0x07) . chr(0x2A) . chr(0x10), array(0x07, 0x2A, 0x10)),
      array('7EF21C', chr(0x7E) . chr(0xF2) . chr(0x1C), array(0x7E, 0xF2, 0x1C)),
    );
  }

  public function providerStringToByteArray() {
    return array(
      array('My name is Foo bar', array(0x4D, 0x79, 0x20, 0x6E, 0x61, 0x6D, 0x65, 0x20, 0x69, 0x73, 0x20, 0x46, 0x6F, 0x6F, 0x20, 0x62, 0x61, 0x72)),
      array('Hello World', array(0x48, 0x65, 0x6C, 0x6C, 0x6F, 0x20, 0x57, 0x6F, 0x72, 0x6C, 0x64)),
      array('This is crazy', array(0x54, 0x68, 0x69, 0x73, 0x20, 0x69, 0x73, 0x20, 0x63, 0x72, 0x61, 0x7A, 0x79)),
      array('01234567890 ha ha', array(0x30, 0x31, 0x32, 0x33, 0x34, 0x35, 0x36, 0x37, 0x38, 0x39, 0x30, 0x20, 0x68, 0x61, 0x20, 0x68, 0x61)),
    );
  }
}
