<?php declare(strict_types=1);

use Uuid\{DateTimeUuid, UuidError};

class DateTimeUuidTest extends PHPUnit\Framework\TestCase
{
    function testConstructor() {
        $uuid = new DateTimeUuid();

        self::assertIsString($uuid->value);
        self::assertEquals($uuid->value, new DateTimeUuid($uuid->value));
        self::assertSame($uuid->value, (new DateTimeUuid($uuid))->value);

        try {
            new DateTimeUuid(null);
        } catch (UuidError $e) {
            self::assertSame("Invalid date/time UUID value: null", $e->getMessage());
        }

        try {
            new DateTimeUuid('invalid');
        } catch (UuidError $e) {
            self::assertSame("Invalid date/time UUID value: 'invalid'", $e->getMessage());
        }
    }

    function testGetDate() {
        $uuid = new DateTimeUuid();
        $date = explode('.', gmdate('Y.m.d'));

        self::assertSame($date, $uuid->getDate());
        self::assertSame(implode('-', $date), $uuid->getDate(separator: '-'));

        $uuid = new DateTimeUuid('d41d8cd98f00b204e9800998ecf8427e', strict: false);

        self::assertNull($uuid->getDate());
    }

    function testGetTime() {
        $uuid = new DateTimeUuid();
        $time = explode('.', gmdate('H.i.s'));

        self::assertSame($time, $uuid->getTime());
        self::assertSame(implode(':', $time), $uuid->getTime(separator: ':'));

        $uuid = new DateTimeUuid('d41d8cd98f00b204e9800998ecf8427e', strict: false);

        self::assertNull($uuid->getTime());
    }

    function testGetDateTime() {
        $uuid = new DateTimeUuid();
        $date = explode('.', gmdate('Y.m.d'));
        $time = explode('.', gmdate('H.i.s'));

        self::assertSame(implode('-', $date), $uuid->getDateTime()->format('Y-m-d'));
        self::assertSame(implode(':', $time), $uuid->getDateTime()->format('H:i:s'));

        $uuid = new DateTimeUuid('d41d8cd98f00b204e9800998ecf8427e', strict: false);

        self::assertNull($uuid->getDateTime());
    }

    function testIsValid() {
        $uuid = new DateTimeUuid();
        $threshold = $this->threshold();

        self::assertTrue($uuid->isValid());
        self::assertFalse($uuid->isValid(threshold: $threshold));
    }

    function testGenerate() {
        $uuid = DateTimeUuid::generate();
        $hash = str_replace('-', '', $uuid);

        self::assertSame(36, strlen($uuid));
        self::assertSame(32, strlen($hash));
        self::assertTrue(ctype_xdigit($hash));

        [$version, $variant] = [$hash[12], $hash[16]];

        self::assertSame($version, '4');
        self::assertContains($variant, ['8','9','a','b']);
    }

    function testValidate() {
        $uuid1 = new DateTimeUuid();
        $uuid2 = new DateTimeUuid('d41d8cd98f00b204e9800998ecf8427e', strict: false);

        self::assertTrue(DateTimeUuid::validate($uuid1->value));
        self::assertFalse(DateTimeUuid::validate($uuid2->value, strict: false));
        self::assertFalse(DateTimeUuid::validate('invalid'));
        self::assertFalse(DateTimeUuid::validate('invalid', strict: false));

        $threshold = $this->threshold();

        self::assertFalse(DateTimeUuid::validate($uuid1->value, threshold: $threshold));
        self::assertFalse(DateTimeUuid::validate($uuid2->value, threshold: $threshold, strict: false));
    }

    function testParse() {
        $uuid1 = new DateTimeUuid();
        $uuid2 = new DateTimeUuid('d41d8cd98f00b204e9800998ecf8427e', strict: false);

        self::assertNotNull(DateTimeUuid::parse($uuid1->value));
        self::assertNull(DateTimeUuid::parse($uuid2->value));

        $threshold = $this->threshold();

        self::assertNull(DateTimeUuid::parse($uuid1->value, threshold: $threshold));
    }

    private function threshold($diff = 1) {
        // Next year to falsify (eg: 20241212191919).
        return (gmdate('Y') + $diff) . '1212191919';
    }
}
