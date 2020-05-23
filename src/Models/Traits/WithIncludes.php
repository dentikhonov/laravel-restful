<?php

namespace Devolt\Restful\Models\Traits;

trait WithIncludes
{
    public static array $itemWith = [];

    public static ?array $collectionWith = null;

    public static array $itemWithCount = [];

    public static ?array $collectionWithCount = null;

    public static function getItemWith(): array
    {
        return static::$itemWith;
    }

    public static function getCollectionWith(): array
    {
        return static::$collectionWith ?? static::getItemWith();
    }

    public static function getItemWithCount(): array
    {
        return static::$itemWithCount;
    }

    public static function getCollectionWithCount(): array
    {
        return static::$collectionWithCount ?? static::getItemWithCount();
    }
}
