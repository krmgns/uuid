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

/** @route /crop/:image */
public function cropAction(string $image) {
    // Eg: 0134b3ce-ce20-4917-a020-f0514e110834.jpg
    [$base, $ext] = explode('.', $image);

    try {
        // Since we've created an image file with DateUuid,
        // we're expecting the incoming $image to be valid.
        $uuid = new DateUuid($base);

        // Eg: 2023/11/12
        $path = $uuid->getDate('/');
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
