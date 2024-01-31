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

    /**
     * Check if given date is valid.
     *
     * @param  int|string|null ...$args
     * @return bool
     */
    public static function isValidDate(int|string|null ...$args): bool
    {
        [$y, $m, $d] = array_map('intval', $args);

        return (
               $y >= 0
            && $m >= 1 && $m <= 12
            && $d >= 1 && $d <= 31
        );
    }

    /**
     * Check if given time is valid.
     *
     * @param  int|string|null ...$args
     * @return bool
     */
    public static function isValidTime(int|string|null ...$args): bool
    {
        [$h, $i, $s] = array_map('intval', $args);

        return (
               $h >= 0 && $h <= 23
            && $i >= 0 && $i <= 59
            && $s >= 0 && $s <= 59
        );
    }

    /**
     * Create a Traversable object with some utilities.
     *
     * @param  string $str
     * @param  int    $len
     * @param  int    $pad
     * @return Traversable
     */
    public static function slit(string $str, int $len, int $pad = null): \Traversable
    {
        return new class(str_split($str, $len), $pad) implements \IteratorAggregate {
            public function __construct(public array $data, int $pad = null) {
                $pad && $this->data = array_pad($this->data, $pad, null);
            }

            public function slice(int $start, int $len = null): self {
                return new self(array_slice($this->data, $start, $len));
            }

            public function getIterator(): \Traversable {
                foreach ($this->data as $i => $item) {
                    yield $i => $item;
                }
            }
        };
    }
}
