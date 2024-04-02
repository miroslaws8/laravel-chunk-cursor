<?php

namespace Itsimiro\ChunkCursor\Providers;

use Illuminate\Support\LazyCollection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;

class LaravelChunkCursorProvider extends ServiceProvider
{
    public function boot(): void
    {
        Builder::macro(
            'chunkCursor',
            function (int $size = 1000) {
                return $this->applyScopes()->query->cursor()->chunk($size)->map(function (LazyCollection $records) {
                    if (count($models = $this->hydrate($records->toArray())) > 0) {
                        $models = $this->eagerLoadRelations($models->all());
                    }

                    return $this->newModelInstance()->newCollection($models);
                });
            }
        );
    }

    public function register(): void
    {}

    public function provides(): array
    {
        return [
            'laravel-chunk-cursor'
        ];
    }
}