<?php

namespace Devolt\Restful\Services;

use Devolt\Restful\Contracts\Restful;
use Devolt\Restful\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

abstract class BaseRestfulService implements Restful
{
    /**
     * @var string|null $model The Model Class name
     */
    protected ?string $model = null;

    protected ?Model $modelInstance = null;

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

    /**
     * @inheritDoc
     */
    public function getModelInstance(): Model
    {
        if (!$this->modelInstance) {
            $this->modelInstance = new $this->model();
        }

        return $this->modelInstance;
    }

    /**
     * @inheritDoc
     */
    public function getPerPage(): ?int
    {
        return $this->getModelInstance()->getPerPage();
    }

    /**
     * @inheritDoc
     * @throws ValidationException
     */
    public function validateResource(Model $resource, ?array $data = null): array
    {
        // If no data is provided, validate the resource against it's present attributes
        if (is_null($data)) {
            $data = $resource->getAttributes();
        } else {
            $data = $this->getAttributesFromData($data);
        }

        $validator = validator($data, $resource->getValidationRules(), $resource->getValidationMessages());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * @inheritDoc
     * @throws ValidationException
     */
    public function validateResourceUpdate(Model $resource, array $data): array
    {
        $validator = validator(
            $this->getAttributesFromData($data),
            $this->getRelevantValidationRulesUpdating($resource, $data),
            $resource->getValidationMessages()
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }


    protected function getRelevantValidationRulesUpdating(Model $resource, array $data): array
    {
        $dataKeys = array_keys($data);
        $rules = $resource->getValidationRulesUpdating();

        $relevantRules = [];
        foreach ($rules as $attribute => $rule) {
            // We only want to compare with the attribute name portion of the rule key (example: only attribute in
            //    attribute.other.irrelevant.items => required)
            // If it matches a key in the data array, then the rule is relevant
            if (in_array(Str::before($attribute, '.'), $dataKeys)) {
                $relevantRules[$attribute] = $rule;
            }
        }

        return $relevantRules;
    }

    abstract protected function getAttributesFromData(array $data): array;

    /**
     * This function can be used to add conditions to the query builder,
     * which will specify the currently logged in user's ownership of the model
     *
     * @param Builder|Model $query
     * @return Builder|null
     */
    protected function qualifyCollectionPolicyQuery($query)
    {
        $user = auth()->user();

        $modelPolicy = Gate::getPolicyFor($this->model);

        // If no policy exists for this model, then there's nothing to check
        if (is_null($modelPolicy)) {
            return $query;
        }

        // Add conditions to the query, if they are defined in the model's policy
        if (method_exists($modelPolicy, 'qualifyCollectionQueryWithUser')) {
            $query = $modelPolicy->qualifyCollectionQueryWithUser($user, $query);
        }

        return $query;
    }

    /**
     * @param Builder $query
     * @return Builder|Model
     */
    protected function qualifyCollectionRelationsQuery($query)
    {
        return $query->with($this->getModelInstance()::getCollectionWith())
            ->withCount($this->getModelInstance()::getCollectionWithCount());
    }
}
