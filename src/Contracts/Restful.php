<?php

namespace Devolt\Restful\Contracts;

use Devolt\Restful\Models\Model;

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

    /**
     * @return Model Instance of model, used in the service
     */
    public function getModelInstance(): Model;

    /**
     * @return string<JsonResource>
     */
    public function getResourceClass(): string;

    /**
     * @return string<ResourceCollection>
     */
    public function getResourceCollectionClass(): string;

    /**
     * @return int|null Items to display per page
     */
    public function getPerPage(): ?int;

    /**
     * @param Model $resource
     * @param array|null $data
     * @return array Validated data
     * @throws ValidationException
     */
    public function validateResource(Model $resource, ?array $data = null): array;

    /**
     * @param Model $resource
     * @param array $data
     * @return array Validated data
     * @throws ValidationException
     */
    public function validateResourceUpdate(Model $resource, array $data): array;

    /**
     * Create base qualified collection query (e.g. index)
     *
     * @return Builder
     */
    public function collectionQuery(): Builder;

    /**
     * @param $model
     * @return Model
     */
    public function singleItemQuery($model): Model;

    /**
     * @param array $input
     * @return Model
     * @throws ValidationException
     */
    public function createInstance(array $input): Model;

    /**
     * @param Model $model
     * @param array $input
     * @return Model
     * @throws ValidationException
     */
    public function updateInstance(Model $model, array $input): Model;

    /**
     * @param Model $model
     * @return boolean|null
     * @throws ValidationException
     * @throws Exception
     */
    public function deleteInstance(Model $model);
}
