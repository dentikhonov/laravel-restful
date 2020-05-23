<?php

namespace Devolt\Restful\Services;

use Devolt\Restful\Contracts\Restful;

abstract class BaseRestfulService implements Restful
{
    /**
     * @var string|null $model The Model Class name
     */
    protected ?string $model = null;

    /**
     * @inheritDoc
     */
    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function getModel(): string
    {
        return $this->model;
    }
}
