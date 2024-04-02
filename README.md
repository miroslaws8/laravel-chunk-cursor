# Laravel Chunk Cursor

This package provides a functionality that adds a `chunkCursor` to the `Illuminate\Database\Eloquent\Builder` class. This allows you to fetch large datasets in chunks using a cursor, reducing memory usage and use eager loading!

## Why use that?

The eloquent method of the cursor builder does not support eager load relations, but with this package you will be able to use `with()` and `load()` as before with all the charms of cursor().

## Installation

You can install the package via composer:

```bash
composer require itsimiro/laravel-chunk-cursor
```

## Usage
Here's an example of how you can use the chunkCursor macro:

With `cursor()`:

```php
use App\Models\User;

User::query()->with(['articles', 'friends'])->cursor(50)->each(function (User $users) {
    // Process user... But 'articles' and 'friend' won't be loaded here :(
});
```

With `chunkCursor()`:

```php
use App\Models\User;

User::query()->with(['articles', 'friends'])->chunkCursor(50)->each(function (Collection $users) {
    foreach ($users as $user) {
        // Process user... With eager loading! :)
    }
});
```

In this example, the users are fetched in chunks of 50. You can adjust the chunk size to suit your needs.

## Compare the execution time

Let's make a comparison without eager loading, and we'll see that chunkCursor is 2 times faster.

With `cursor()`:

```php
$startCursor = microtime(true);

DatabaseChunkCursorTestUser::query()->cursor()->each(function (DatabaseChunkCursorTestUser $user) use (&$i) {
    $this->assertEquals(true, true);
});

$endCursor = microtime(true);

$timeCursor = number_format($endCursor - $startCursor, 6); // 0.006624
```

With `chunkCursor()`:

```php
$startChunkCursor = microtime(true);

// 1000 rows
DatabaseChunkCursorTestUser::query()->chunkCursor(10)->each(function (Collection $users) use (&$i) {
    $i += 10;
    $this->assertLessThanOrEqual(10, $users->count());
});

$endChunkCursor = microtime(true);

$timeChunkCursor = number_format($endChunkCursor - $startChunkCursor, 6); // 0.003406
```

As you can see, if we add relationship loading, cursor will work even slower, because it will have to make a new request to the database every time to get data.

## Testing
To run the tests for this package, use the following command:
```bash
composer test
```