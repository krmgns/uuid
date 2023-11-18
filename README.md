While preserving version & variant fields of generated values, Uuid library provides three types of UUIDs with a simple and fast approach, and can be used where sortable UUIDs are needed.

The `generate()` method of;

- `Uuid\Uuid` class uses 16-length random bytes.
- `Uuid\DateUuid` class uses 12-length random bytes and 4-length bytes of UTC date as prefix.
- `Uuid\DateTimeUuid` class uses 10-length random bytes and 6-length bytes of UTC date as prefix.

Besides these UUIDs are sortable, they can be used for some sort of jobs like folder exploration (say, where we are working with a image cropping service).

So, as a quick example, let's see it in action, yet in case you can more examples in [test/unit](/test/unit) directory;

```php
// File: ImageController.php
use Uuid\{DateUuid, UuidError};
use Throwable;

/**
 * Eg: api.foo.com/image/crop/0134b3ce-ce20-4917-a020-f0514e110834.jpg
 * @route /image/crop/:image
 */
public function cropAction(string $image) {
    // Eg: 0134b3ce-ce20-4917-a020-f0514e110834.jpg
    [$base, $ext] = explode('.', $image);

    try {
        // Since we've created an image file with DateUuid,
        // we're expecting the incoming $image to be valid.
        $uuid = new DateUuid($base);

        // Eg: 2023/11/12
        $path = $uuid->getDate(separator: '/');
    } catch (UuidError) {
        // Invalid DateUuid.
        throw new BadRequestError();
        // Internal error.
    } catch (Throwable) {
        throw new InternalServerError();
    }

    // Eg: /images/2023/11/12/0134b3ce-ce20-4917-a020-f0514e110834.jpg
    $image = sprintf('/images/%s/%s.%s', $path, $base, $ext);

    // So such file.
    if (!is_file($image)) {
        throw new NotFoundError();
    }

    // Else crop image & serve cropped image here...
}
```

### Installing
```
composer require okerem/uuid
```

### Notes & Reminding

· Besides all classes can take `$value` argument (#1) as type of `string`, `Uuid\Uuid` class can also take type of `Uuid\Uuid`, `Uuid\DateUuid` class can also take type of `Uuid\DateUuid`, `Uuid\DateTimeUuid` class can also take type of `Uuid\DateTimeUuid`, but it also can be skipped for auto-generation at the same time.

· Besides `Uuid\Uuid` is implementing `Stringable` interface, `Uuid\DateUuid` and `Uuid\DateTimeUuid` are subclasses of `Uuid\Uuid` class. So, while inheriting some useful methods (`toString()`, `toHashString()`, etc.), they also overrides some methods (`isValid()`, `generate()`, `validate()` etc.) alongside `__constructor()` methods.

### The `Uuid\Uuid` Class

Like the inheriting classes, when no `$value` (UUID value) given, `Uuid\Uuid` class will generate and assign its value by itself. Otherwise, given value will be assigned after it's checked in strict mode (modifier argument is `$strict` as `true`) whether it's a valid UUID value or not.

```php
use Uuid\{Uuid, UuidError};

// Auto-generate.
$uuid = new Uuid();

assert($uuid->value === $uuid->toString());
assert($uuid->value === (string) $uuid);
assert($uuid->value == $uuid); // Stringable

assert(true === $uuid->isEqual($uuid));
assert(true === $uuid->isEqual($uuid->value));

$uuid = new Uuid('26708ec6-ad78-4291-a449-9ee08cf50cfc');
assert(true === $uuid->isValid());

$uuid = new Uuid('invalid', strict: false);
assert(false === $uuid->isValid());

try { new Uuid(null); } catch (UuidError $e) {
    assert("Invalid UUID value: null" === $e->getMessage());
}

try { new Uuid('invalid'); } catch (UuidError $e) {
    assert("Invalid UUID value: 'invalid'" === $e->getMessage());
}

// Given value.
$uuid = new Uuid($value = '26708ec6-ad78-4291-a449-9ee08cf50cfc');

assert(true === $uuid->isEqual($uuid));
assert(true === $uuid->isEqual($uuid->value));
assert(true === $uuid->isEqual($value));

assert('26708ec6-ad78-4291-a449-9ee08cf50cfc' === $uuid->toString());
assert('26708ec6ad784291a4499ee08cf50cfc' === $uuid->toHashString());

// Null values.
$uuid1 = new Uuid('00000000-0000-0000-0000-000000000000', strict: false);
$uuid2 = new Uuid('00000000000000000000000000000000', strict: false);

assert(false === $uuid1->isValid());
assert(false === $uuid2->isValid());

assert(true === $uuid1->isNull());
assert(true === $uuid2->isNullHash());

assert(Uuid::NULL === $uuid1->value);
assert(Uuid::NULL_HASH === $uuid2->value);
```

#### Statics

```php
// Generating.
$uuid = Uuid::generate(); // Eg: fec3cfe2-d378-4181-8ba1-99c54bcfa63e

// Validating.
$valid = Uuid::validate($uuid->value);
assert(true === $valid);

assert(false === Uuid::validate('invalid'));
assert(false === Uuid::validate('invalid', strict: false));

assert(false === Uuid::validate(Uuid::NULL));
assert(false === Uuid::validate(Uuid::NULL_HASH));

assert(true === Uuid::validate(Uuid::NULL, strict: false));
assert(true === Uuid::validate(Uuid::NULL_HASH, strict: false));

// Equal checking.
assert(true === Uuid::equals($uuid->value, 'fec3cfe2-d378-4181-8ba1-99c54bcfa63e'));
assert(false === Uuid::equals($uuid->value, 'invalid-uuid-input-value'));

// DIY tools.
$bins = random_bytes(16);

// Add version/variant.
$bins = Uuid::modify($bins);

// Format as UUID format.
$uuid = Uuid::format(bin2hex($bins));
```

See [test/unit/UuidTest.php](test/unit/UuidTest.php) for more examples. <br><br>

### The `Uuid\DateUuid` Class

This class uses 12-length random bytes and 4-length bytes of UTC date as prefix. So, its date can be re-taken (eg: 20231212 or 2023-12-12 with separator) to use for any use case and it's usable for where sortable UUIDs are needed.

```php
use Uuid\DateUuid;

$dates = [gmdate('Ymd'), gmdate('Y-m-d')];

// Getting date.
$uuid = new DateUuid();

assert($dates[0] === $uuid->getDate());
assert($dates[1] === $uuid->getDate(separator: '-'));

$uuid = new DateUuid(md5(''), strict: false);

assert(null === $uuid->getDate());

// Getting date/time.
$uuid = new DateUuid();

assert($dates[0] === $uuid->getDateTime()->format('Ymd'));
assert($dates[1] === $uuid->getDateTime()->format('Y-m-d'));

$uuid = new DateUuid(md5(''), strict: false);

assert(null === $uuid->getDateTime());
```

#### Statics

```php
// Generating.
$uuid = DateUuid::generate(); // Eg: 0134b3ce-25fc-49f8-b9f9-61ed2784c7d1

// Parsing.
$uuid1 = new DateUuid();
$uuid2 = new DateUuid(md5(''), strict: false);

assert(null !== DateUuid::parseDate($uuid1->value));
assert(null === DateUuid::parseDate($uuid2->value));

// Next year for falsity (eg: 20241212).
$threshold = (gmdate('Y') + $diff) . '1212';

assert(null === DateUuid::parseDate($uuid1->value, threshold: $threshold));
```

See [test/unit/DateUuidTest.php](test/unit/DateUuidTest.php) for more examples. <br><br>
