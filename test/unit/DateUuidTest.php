<?php declare(strict_types=1);

use Uuid\{DateUuid, UuidError};

class DateUuidTest extends PHPUnit\Framework\TestCase
{
    function testConstructor() {
        $uuid = new DateUuid();

        self::assertIsString($uuid->value);
        self::assertEquals($uuid->value, new DateUuid($uuid->value));
        self::assertSame($uuid->value, (new DateUuid($uuid))->value);

        try {
            new DateUuid(null);
        } catch (UuidError $e) {
            self::assertSame("Invalid date UUID value: null", $e->getMessage());
        }

        try {
            new DateUuid('invalid');
        } catch (UuidError $e) {
            self::assertSame("Invalid date UUID value: 'invalid'", $e->getMessage());
        }
    }

    function testGetDate() {
        $uuid = new DateUuid();
        $date = explode('.', gmdate('Y.m.d'));

        self::assertSame($date, $uuid->getDate());
        self::assertSame(implode('-', $date), $uuid->getDate(separator: '-'));

        $uuid = new DateUuid('d41d8cd98f00b204e9800998ecf8427e', strict: false);

        self::assertNull($uuid->getDate());
    }

    function testGetDateTime() {
        $uuid = new DateUuid();
        $date = explode('.', gmdate('Y.m.d'));
        $time = explode('.', gmdate('00.00.00'));

        self::assertSame(implode('-', $date), $uuid->getDateTime()->format('Y-m-d'));
        self::assertSame(implode(':', $time), $uuid->getDateTime()->format('H:i:s'));

        $uuid = new DateUuid('d41d8cd98f00b204e9800998ecf8427e', strict: false);

        self::assertNull($uuid->getDateTime());
    }

    function testIsValid() {
        $uuid = new DateUuid();
        $threshold = $this->threshold();

        self::assertTrue($uuid->isValid());
        self::assertFalse($uuid->isValid(threshold: $threshold));
    }

    function testGenerate() {
        $uuid = DateUuid::generate();
        $hash = str_replace('-', '', $uuid);

        self::assertSame(36, strlen($uuid));
        self::assertSame(32, strlen($hash));
        self::assertTrue(ctype_xdigit($hash));

        [$version, $variant] = [$hash[12], $hash[16]];

        self::assertSame($version, '4');
        self::assertContains($variant, ['8','9','a','b']);
    }

    function testValidate() {
        $uuid1 = new DateUuid();
        $uuid2 = new DateUuid('d41d8cd98f00b204e9800998ecf8427e', strict: false);

        self::assertTrue(DateUuid::validate($uuid1->value));
        self::assertFalse(DateUuid::validate($uuid2->value, strict: false));
        self::assertFalse(DateUuid::validate('invalid'));
        self::assertFalse(DateUuid::validate('invalid', strict: false));

        $threshold = $this->threshold();

        self::assertFalse(DateUuid::validate($uuid1->value, threshold: $threshold));
        self::assertFalse(DateUuid::validate($uuid2->value, threshold: $threshold, strict: false));
    }

    function testParse() {
        $uuid1 = new DateUuid();
        $uuid2 = new DateUuid('d41d8cd98f00b204e9800998ecf8427e', strict: false);

        self::assertNotNull(DateUuid::parse($uuid1->value));
        self::assertNull(DateUuid::parse($uuid2->value));

        $threshold = $this->threshold();

        self::assertNull(DateUuid::parse($uuid1->value, threshold: $threshold));
    }

    private function threshold($diff = 1) {
        // Next year to falsify (eg: 20241212).
        return (gmdate('Y') + $diff) . '1212';
    }
}
