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
    [$base, $ext] = explode('.', $image);

    // Since we've created an image file with DateUuid,
    // we're expecting the incoming $image to be valid.
    try {
        $uuid = new DateUuid($base);
        $path = $uuid->getDate('/');
    } catch (UuidError) {
        throw new BadRequestError();
    } catch (Throwable) {
        throw new InternalServerError();
    }

    $image = sprintf('/images/%s/%s.%s', $path, $base, $ext);
    if (!is_file($image)) {
        throw new NotFoundError();
    }

    // Crop image & serve cropped image here...
}
```
