<?php

namespace KodeHauz\Utility;

class Byte {

  /**
   * Reads a variable length string to int (+repositioning).
   */
  public static function readVarLen($str, &$pos) {
    if (($value = ord($str[$pos++])) & 0x80) {
      $value &= 0x7F;
      do {
        $value = ($value << 7) + (($c = ord($str[$pos++])) & 0x7F);
      } while ($c & 0x80);
    }
    return ($value);
  }

  /**
   * Writes an int to a variable length string.
   */
  public static function writeVarLen($value) {
    $buf = $value & 0x7F;
    $str = '';
    while (($value >>= 7)) {
      $buf <<= 8;
      $buf |= (($value & 0x7F) | 0x80);
    }
    while (TRUE) {
      $str .= chr($buf % 256);
      if ($buf & 0x80) {
        $buf >>= 8;
      }
      else {
        break;
      }
    }
    return $str;
  }

  /**
   * Gets the specified value as a variable length array of bytes.
   */
  public static function getVarLen($value) {
    $buf = $value & 0x7F;
    $bytes = array();
    while (($value >>= 7)) {
      $buf <<= 8;
      $buf |= (($value & 0x7F) | 0x80);
    }
    while (TRUE) {
      $bytes[] = $buf % 256;
      if ($buf & 0x80) {
        $buf >>= 8;
      }
      else {
        break;
      }
    }
    return $bytes;
  }

  /**
   * Converts a hexadecimal string to a binary character string.
   */
  public static function hexToBinaryString($hex_str) {
    $bin_str = '';
    for ($i = 0; $i < strlen($hex_str); $i += 2) {
      $bin_str .= chr(hexdec(substr($hex_str, $i, 2)));
    }
    return $bin_str;
  }

  /**
   * Converts a hexadecimal string to an array of numbers.
   */
  public static function hexToBinaryArray($hex_str) {
    $binary_array = array();
    for ($i = 0; $i < strlen($hex_str); $i += 2) {
      $binary_array[] = hexdec(substr($hex_str, $i, 2));
    }
    return $binary_array;
  }

  /** int to bytes (length $len) */
  public static function getBytes($n, $len) {
    $str = '';
    for ($i = $len - 1; $i >= 0; $i--) {
      $str .= chr(floor($n / pow(256, $i)));
    }
    return $str;
  }

  public static function stringToByteArray($string) {
    $bytes = array();
    for ($i = 0; $i < strlen($string); $i++) {
      $bytes[] = ord($string[$i]);
    }
    return $bytes;
  }

}
