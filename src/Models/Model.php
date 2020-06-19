<?php

namespace Devolt\Restful\Models;

use Devolt\Restful\Http\Responses\JsonApiResource;
use Devolt\Restful\Http\Responses\JsonApiResourceCollection;
use Devolt\Restful\Models\Traits\WithIncludes;
use Devolt\Restful\Models\Traits\WithValidation;
use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    use WithValidation;
    use WithIncludes;

    /** @var string<JsonResource> $resource */
    public static string $resource = JsonApiResource::class;

    /** @var string<ResourceCollection> $resourceCollection */
    public static string $resourceCollection = JsonApiResourceCollection::class;

    public function getResource(): string
    {
        return static::$resource ?? JsonApiResource::class;
    }

    public function getResourceCollection(): string
    {
        return static::$resourceCollection ?? JsonApiResourceCollection::class;
    }
}
