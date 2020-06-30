<?php

namespace Devolt\Restful\Services;

use Devolt\Restful\Contracts\Restful;
use Devolt\Restful\Models\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\UnauthorizedException;
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
    public function setModelInstance(Model $modelInstance): void
    {
        $this->modelInstance = $modelInstance;
    }

    /**
     * @inheritDoc
     */
    public function createInstance(array $input): Model
    {
        return $this->getModelInstance()->create($this->validateResource($this->getModelInstance(), $input));
    }

    /**
     * @inheritDoc
     */
    public function updateInstance(Model $model, array $input): Model
    {
        $model->update($this->validateResourceUpdate($model, $input));

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function deleteInstance(Model $model)
    {
        return $model->delete();
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
    public function validateResource($resource, ?array $data = null): array
    {
        if (is_array($resource) && empty($data)) {
            $data = $resource;
            $resource = $this->getModelInstance();
        }

        // If no data is provided, validate the resource against it's present attributes
        $data = is_null($data) ? $resource->getAttributes() : $this->getAttributesFromData($data);

        // Check, if user can update field
        $data = $this->authorizeFields($data, $resource, auth()->user());

        $validator = validator(
            $data,
            $resource->getValidationRules(),
            $resource->getValidationMessages()
        );

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
        // If no data is provided, validate the resource against it's present attributes
        $data = is_null($data) ? $resource->getChanges() : $this->getAttributesFromData($data);

        // Check, if user can update field
        $data = $this->authorizeFields($data, $resource, auth()->user());

        $validator = validator(
            $data,
            $this->getRelevantValidationRulesUpdating($resource, $data),
            $resource->getValidationMessages()
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    protected function authorizeFields($fields, Model $model, Authenticatable $user)
    {
        $modelPolicy = $this->getPolicyFor($this->model);

        if (method_exists($modelPolicy, 'updateField')) {
            $rules = $modelPolicy->updateField($user, $model);

            $fields = collect($fields)
                ->filter(function ($value, $key) use ($model, $rules) {
                    if (empty($rule = $rules[$key]) && $rule !== false) {
                        return true;
                    }

                    $ability = is_callable($rule)
                        ? call_user_func($rule, [$value, $model->getOriginal($key)])
                        : $rule;

                    if (!$ability) {
                        throw new UnauthorizedException(__('authorization.field.unauthorized', [
                            'field' => $key
                        ]));
                    }

                    return $ability;
                })
                ->toArray();
        }

        return $fields;
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

    public function getAttributesFromData(array $data): array
    {
        return $data;
    }

    /**
     * @var Model $model
     * @inheritDoc
     */
    public function singleItemQuery($model): Model
    {
        $model->load($model::getItemWith());
        $model->loadCount($model::getItemWithCount());

        return $this->processItem($model);
    }

    public function processItem(Model $model): Model
    {
        if (!empty($fields = $this->getHiddenFieldsFor($model, auth()->user()))) {
            $model = $model->makeHidden($fields);
        }

        return $model;
    }

    public function getHiddenFieldsFor(Model $model, Authenticatable $user): array
    {
        $modelPolicy = $this->getPolicyFor($this->getModel());

        if (method_exists($modelPolicy, 'viewField')) {
            return collect($modelPolicy->viewField($user, $model))
                ->filter(fn($value) => !$value)
                ->map(fn($value, $key) => $key)
                ->values()
                ->toArray();
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function collectionQuery(): Builder
    {
        $query = $this->qualifyCollectionPolicyQuery($this->getModelInstance()->newModelQuery());
        $query = $this->qualifyCollectionRelationsQuery($query);

        return $query;
    }

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

        $modelPolicy = $this->getPolicyFor($this->model);

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

    protected function getPolicyFor(string $class)
    {
        return Gate::getPolicyFor($class);
    }
}
