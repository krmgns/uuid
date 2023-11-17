<?php declare(strict_types=1);
/**
 * Copyright (c) 2023 · Kerem Güneş
 * Apache License 2.0 · https://github.com/okerem/uuid
 */
namespace Uuid;

/**
 * @package Uuid
 * @class   Uuid\UuidError
 * @author  Kerem Güneş
*/
class UuidError extends \Error
{
    public static function forInvalidValue(mixed $value): static
    {
        return ($value === null)
            ? new static("Invalid UUID value: null")
            : new static("Invalid UUID value: '{$value}'");
    }

    public static function forInvalidDateValue(mixed $value): static
    {
        return ($value === null)
            ? new static("Invalid date UUID value: null")
            : new static("Invalid date UUID value: '{$value}'");
    }

    public static function forInvalidDateTimeValue(mixed $value): static
    {
        return ($value === null)
            ? new static("Invalid date/time UUID value: null")
            : new static("Invalid date/time UUID value: '{$value}'");
    }

    public static function forInvalidBins(): static
    {
        return new static('Modify for only 16-length bins');
    }

    public static function forInvalidHash(): static
    {
        return new static('Format for only 32-length UUIDs');
    }
}
