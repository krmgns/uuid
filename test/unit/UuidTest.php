<?php declare(strict_types=1);

use Uuid\{Uuid, UuidError};

class UuidTest extends PHPUnit\Framework\TestCase
{
    function testConstructor() {
        $uuid = new Uuid();

        self::assertIsString($uuid->value);
        self::assertEquals($uuid->value, new Uuid($uuid->value));
        self::assertSame($uuid->value, (new Uuid($uuid))->value);

        try {
            new Uuid(null);
        } catch (UuidError $e) {
            self::assertSame("Invalid UUID value: null", $e->getMessage());
        }

        try {
            new Uuid('invalid');
        } catch (UuidError $e) {
            self::assertSame("Invalid UUID value: 'invalid'", $e->getMessage());
        }
    }

    function testStringCast() {
        $uuid = new Uuid();

        self::assertIsString((string) $uuid);
        self::assertSame((string) $uuid, $uuid->value);
    }

    function testStringGetters() {
        $uuid = new Uuid();

        self::assertSame($uuid->value, $uuid->toString());
        self::assertSame(str_replace('-', '', $uuid->value), $uuid->toHashString());
    }

    function testIsNullCheckers() {
        $uuid1 = new Uuid('00000000-0000-0000-0000-000000000000', strict: false);
        $uuid2 = new Uuid('00000000000000000000000000000000', strict: false);

        self::assertTrue($uuid1->isNull());
        self::assertTrue($uuid2->isNullHash());

        self::assertSame($uuid1->value, Uuid::NULL);
        self::assertSame($uuid2->value, Uuid::NULL_HASH);
    }

    function testIsEqual() {
        $uuid1 = new Uuid();
        $uuid2 = new Uuid($uuid1->value);

        self::assertTrue($uuid1->isEqual($uuid1));
        self::assertTrue($uuid1->isEqual($uuid1->value));

        self::assertTrue($uuid1->isEqual($uuid2));
        self::assertTrue($uuid1->isEqual($uuid2->value));
    }

    function testIsValid() {
        $uuid1 = new Uuid();
        $uuid2 = new Uuid('invalid', strict: false);

        self::assertTrue($uuid1->isValid());
        self::assertFalse($uuid2->isValid());
    }

    function testGenerate() {
        $uuid = Uuid::generate();
        $hash = str_replace('-', '', $uuid);

        self::assertSame(36, strlen($uuid));
        self::assertSame(32, strlen($hash));
        self::assertTrue(ctype_xdigit($hash));

        [$version, $variant] = [$hash[12], $hash[16]];

        self::assertSame($version, '4');
        self::assertContains($variant, ['8','9','a','b']);
    }

    function testValidate() {
        $uuid1 = new Uuid();
        $uuid2 = new Uuid('d41d8cd98f00b204e9800998ecf8427e', strict: false);

        self::assertTrue(Uuid::validate($uuid1->value));
        self::assertTrue(Uuid::validate($uuid2->value, strict: false));
        self::assertFalse(Uuid::validate('invalid'));
        self::assertFalse(Uuid::validate('invalid', strict: false));
    }

    function testEquals() {
        $uuid1 = new Uuid();
        $uuid2 = new Uuid($uuid1);

        self::assertTrue(Uuid::equals($uuid1->value, $uuid2->value));
        self::assertFalse(Uuid::equals($uuid1->value, 'invalid'));
    }

    function testModify() {
        $bins = Uuid::modify(random_bytes(16));

        self::assertIsString($bins);
        self::assertSame(16, strlen($bins));

        self::expectException(UuidError::class);
        self::expectExceptionMessage('Modify for only 16-length bins');

        Uuid::modify('invalid');
    }

    function testFormat() {
        $hash = Uuid::format(bin2hex(random_bytes(16)));

        self::assertIsString($hash);
        self::assertSame(36, strlen($hash));

        self::expectException(UuidError::class);
        self::expectExceptionMessage('Format for only 32-length hashes');

        Uuid::format('invalid');
    }
}
