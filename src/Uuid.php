<?php declare(strict_types=1);
/**
 * Copyright (c) 2023 · Kerem Güneş
 * Apache License 2.0 · https://github.com/okerem/uuid
 */
namespace Uuid;

/**
 * Base UUID class, generates random (v4) values.
 *
 * @package Uuid
 * @class   Uuid\Uuid
 * @author  Kerem Güneş
 */
class Uuid implements \Stringable
{
    /** Nulls. */
    public final const NULL = '00000000-0000-0000-0000-000000000000',
                       NULL_HASH = '00000000000000000000000000000000';

    /** Given/generated value. */
    public readonly string $value;

    /**
     * Constructor.
     *
     * @param  Uuid\Uuid|null $value
     * @param  bool           $strict
     * @throws Uuid\UuidError If strict & an invalid value given.
     */
    public function __construct(string|Uuid $value = null, bool $strict = true)
    {
        // Check if value given & strict.
        if (func_num_args() && $strict && !self::validate((string) $value, $strict)) {
            throw UuidError::forInvalidValue($value);
        }
        prd($value);

        // Create if none given.
        $value ??= self::generate();

        $this->value = (string) $value;
    }

    /**
     * @magic
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Get Uuid value.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Get Uuid value as dash-freed.
     *
     * @return string
     */
    public function toPlainString(): string
    {
        return str_replace('-', '', $this->value);
    }

    /**
     * Check null value.
     *
     * @return bool
     */
    public function isNull(): bool
    {
        return hash_equals(self::NULL, $this->value);
    }

    /**
     * Check null-hash value.
     *
     * @return bool
     */
    public function isNullHash(): bool
    {
        return hash_equals(self::NULL_HASH, $this->value);
    }

    /**
     * Check whether given Uuid is equal to this value.
     *
     * @param  string|Uuid\Uuid $uuid
     * @return bool
     */
    public function isEqual(string|Uuid $uuid): bool
    {
        return self::equals($this->value, (string) $uuid);
    }

    /**
     * Check whether this Uuid value is valid.
     *
     * @param  bool $strict
     * @return bool
     */
    public function isValid(bool $strict = true): bool
    {
        return self::validate($this->value, $strict);
    }

    /**
     * Generate a random UUID value.
     *
     * @return string
     */
    public static function generate(): string
    {
        // 16-len random bytes.
        $bins = random_bytes(16);

        // Add version/variant.
        $bins = self::modify($bins);

        return self::format(bin2hex($bins));
    }

    /**
     * Check given UUID value if valid.
     *
     * @param  string $uuid
     * @param  bool   $strict
     * @return bool
     */
    public static function validate(string $uuid, bool $strict = true): bool
    {
        if ($strict) {
            // With version, variant & dashes.
            return !!preg_match(
                '~^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[ab89][a-f0-9]{3}-[a-f0-9]{12}$~i',
                $uuid
            );
        }

        // With/without version, variant & dashes.
        return !!preg_match(
            '~^[a-f0-9]{8}-?[a-f0-9]{4}-?[a-f0-9]{4}-?[a-f0-9]{4}-?[a-f0-9]{12}$~i',
            $uuid
        );
    }

    /**
     * Check given UUID values if equals.
     *
     * @param  string $uuidKnown
     * @param  string $uuidUnknown
     * @return bool
     */
    public static function equals(string $uuidKnown, string $uuidUnknown): bool
    {
        return hash_equals($uuidKnown, $uuidUnknown);
    }

    /**
     * Modify given binary input adding version & variant orders.
     *
     * @param  string $bins
     * @return string
     * @throws Uuid\UuidError If bins have no 16-len.
     */
    public static function modify(string $bins): string
    {
        if (strlen($bins) !== 16) {
            throw UuidError::forInvalidBins();
        }

        // Add signs: 4 (version) & 8,9,a,b (variant).
        $bins[6] = chr(ord($bins[6]) & 0x0F | 0x40); // Version.
        $bins[8] = chr(ord($bins[8]) & 0x3F | 0x80); // Variant.

        return $bins;
    }

    /**
     * Format given hash input as formal UUID format.
     *
     * @param  string $hash
     * @return string
     * @throws Uuid\UuidError If hash has no 32-len or not xdigit.
     */
    public static function format(string $hash): string
    {
        if (strlen($hash) !== 32 || !ctype_xdigit($hash)) {
            throw UuidError::forInvalidHash();
        }

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($hash, 4));
    }
}
