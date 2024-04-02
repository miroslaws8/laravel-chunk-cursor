<?php

namespace Itsimiro\ChunkCursor\Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Schema\Builder;
use Illuminate\Foundation\Application;
use Itsimiro\ChunkCursor\Providers\LaravelChunkCursorProvider;
use PHPUnit\Framework\TestCase;

class DatabaseChunkCursorTest extends TestCase
{
    protected function setUp(): void
    {
        $db = new DB();

        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        (new LaravelChunkCursorProvider(new Application()))->boot();

        $db->bootEloquent();
        $db->setAsGlobal();

        $this->createSchema();
    }

    /**
     * Setup the database schema.
     *
     * @return void
     */
    public function createSchema(): void
    {
        $this->schema()->create('users', function ($table) {
            $table->increments('id');
            $table->string('email')->unique();
        });
    }

    public function testDatabaseChunkCursor(): void
    {
        $this->seedData();

        $i = 0;

        DatabaseChunkCursorTestUser::query()->chunkCursor(50)->each(function (Collection $users) use (&$i) {
            $i += 50;
            $this->assertLessThanOrEqual(50, $users->count());
        });

        $this->assertEquals(100, $i);
    }

    protected function tearDown(): void
    {
        $this->schema()->drop('users');
    }

    protected function connection(): ConnectionInterface
    {
        return Eloquent::getConnectionResolver()->connection();
    }

    protected function seedData(): void
    {
        $data = [];

        foreach (range(1, 100) as $i) {
            $data[] = ['id' => $i, 'email' => "test$i@gmail.com"];
        }

        DatabaseChunkCursorTestUser::query()->insert($data);
    }

    protected function schema(): Builder
    {
        return $this->connection()->getSchemaBuilder();
    }
}

class DatabaseChunkCursorTestUser extends Eloquent
{
    protected $table = 'users';
    protected $fillable = ['id', 'email'];
    public $timestamps = false;
}
