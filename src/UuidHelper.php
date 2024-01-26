<?php declare(strict_types=1);
/**
 * Copyright (c) 2023 · Kerem Güneş
 * Apache License 2.0 · https://github.com/krmgns/uuid
 */
namespace Uuid;

/**
 * Helper class (not in use).
 *
 * @package Uuid
 * @class   Uuid\UuidHelper
 * @author  Kerem Güneş
 * @static
*/
class UuidHelper
{
    /**
     * Ensure that the given hex string has length up to the given maxlen.
     *
     * @param  string $hex
     * @param  int    $maxlen
     * @return string
     */
    public static function toEvenLength(string $hex, int $maxlen): string
    {
        static $chars = '0123456789abcdef';

        while (true) {
            $len = strlen($hex);

            if (($len >= $maxlen) && !($len % 2)) {
                break;
            }

            $hex .= $chars[rand(0, 15)];
        }

        return $hex;
    }
}
