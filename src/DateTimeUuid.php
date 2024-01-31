<?php declare(strict_types=1);
/**
 * Copyright (c) 2023 · Kerem Güneş
 * Apache License 2.0 · https://github.com/krmgns/uuid
 */
namespace Uuid;

/**
 * DateTime UUID class, generates date/time prefixed values.
 *
 * @package Uuid
 * @class   Uuid\DateTimeUuid
 * @author  Kerem Güneş
 */
class DateTimeUuid extends Uuid
{
    /** For a valid parsing process. */
    public readonly string|int|null $threshold;

    /**
     * Constructor.
     *
     * @param  string|Uuid\Uuid|Uuid\DateTimeUuid|null $value
     * @param  bool                                    $strict
     * @param  string|int|null                         $threshold
     * @throws Uuid\UuidError If strict & invalid date/time value given.
     * @override
     */
    public function __construct(string|Uuid|DateTimeUuid $value = null, bool $strict = true, string|int $threshold = null)
    {
        // Check if value given & strict.
        if (func_num_args() && $strict && !self::validate((string) $value, $strict, $threshold)) {
            throw UuidError::forInvalidDateTimeValue($value);
        }

        $this->threshold = $threshold;

        // Create if none given.
        $value ??= self::generate();

        parent::__construct($value, false);
    }

    /**
     * Get date.
     *
     * @param  string|null $separator
     * @return array<string>|string|null
     */
    public function getDate(string $separator = null): array|string|null
    {
        [$date] = self::parse($this->value, $this->threshold) ?: [null];

        if ($date && $separator) {
            $date = vsprintf("%s{$separator}%s{$separator}%s", $date);
        }

        return $date;
    }

    /**
     * Get time.
     *
     * @param  string|null $separator
     * @return array<string>|string|null
     */
    public function getTime(string $separator = null): array|string|null
    {
        [, $time] = self::parse($this->value, $this->threshold) ?: [null, null];

        if ($time && $separator) {
            $time = vsprintf("%s{$separator}%s{$separator}%s", $time);
        }

        return $time;
    }

    /**
     * Get date/time.
     *
     * @param  string|null $zone
     * @return DateTime|null
     * @throws Uuid\UuidError
     */
    public function getDateTime(string $zone = null): \DateTime|null
    {
        [$date, $time] = self::parse($this->value, $this->threshold) ?: [null, null];

        if ($date !== null && $time !== null) {
            try {
                $ret = new \DateTime(join($date) . join($time), new \DateTimeZone('UTC'));

                // Convert to zone.
                if ($zone !== null) {
                    $ret->setTimezone(new \DateTimeZone($zone));
                }

                return $ret;
            } catch (\Throwable $e) {
                throw new UuidError($e->getMessage(), $e->getCode(), $e);
            }
        }

        return null;
    }

    /**
     * Check this Uuid value if valid.
     *
     * @param  bool            $strict
     * @param  string|int|null $threshold
     * @return bool
     * @override
     */
    public function isValid(bool $strict = true, string|int $threshold = null): bool
    {
        return self::validate(
            $this->value, $strict,
            $threshold ?? $this->threshold
        );
    }

    /**
     * Generate a date/time prefixed UUID value.
     *
     * @return string
     * @override
     */
    public static function generate(): string
    {
        $date = self::datetime();

        // Q for 64-bit ulong.
        $bins = pack('Q', $date);

        // Drop NUL-padding.
        $bins = substr($bins, 0, -2);

        // Reverse & add random bytes.
        $bins = strrev($bins) . random_bytes(10);

        // Add version/variant.
        $bins = parent::modify($bins);

        return parent::format(bin2hex($bins));
    }

    /**
     * Check given UUID value if valid.
     *
     * @param  string          $uuid
     * @param  bool            $strict
     * @param  string|int|null $threshold
     * @return bool
     * @override
     */
    public static function validate(string $uuid, bool $strict = true, string|int $threshold = null): bool
    {
        if (!parent::validate($uuid, $strict)) {
            return false;
        }
        if (!self::parse($uuid, $threshold)) {
            return false;
        }

        return true;
    }

    /**
     * Parse date/time from given UUID value.
     *
     * @param  string          $uuid
     * @param  string|int|null $threshold
     * @return array<array<string>>|null
     */
    public static function parse(string $uuid, string|int $threshold = null): array|null
    {
        $ret = null;

        // Extract usable part from value.
        if (ctype_xdigit($sub = substr(strtr($uuid, ['-' => '']), 0, 12))) {
            $dec = hexdec($sub);
            $tmp = UuidHelper::slit((string) $dec, 2, pad: 7);
            [$y, $m, $d, $h, $i, $s] = [join([...$tmp->slice(0, 2)]), ...$tmp->slice(2)];

            // Validate.
            if (UuidHelper::isValidDate($y, $m, $d) && UuidHelper::isValidTime($h, $i, $s)){
                $ret = [[$y, $m, $d], [$h, $i, $s]];
            }
        }

        // Validate.
        if ($ret !== null) {
            if ($threshold && $dec < $threshold) {
                return null;
            }
            if ($dec > self::datetime()) {
                return null;
            }
        }

        return $ret;
    }

    /**
     * Get current UTC date/time.
     *
     * @return string
     */
    public static function datetime(): string
    {
        return gmdate('YmdHis');
    }
}
