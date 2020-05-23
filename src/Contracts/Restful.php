<?php

namespace Devolt\Restful\Contracts;

interface Restful
{
    /**
     * Set model to be used in the service
     *
     * @param string $model
     */
    public function setModel(string $model): void;

    /**
     * @return string Model, used in the service
     */
    public function getModel(): string;
}
