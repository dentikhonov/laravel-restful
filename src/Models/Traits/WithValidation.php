<?php

namespace Devolt\Restful\Models\Traits;

trait WithValidation
{
    /**
     * Return the validation rules for this model
     *
     * @return array Validation rules to be used for the model when creating it
     */
    public function getValidationRules()
    {
        return [];
    }

    /**
     * Return the validation rules for this model's update operations
     * In most cases, they will be the same as for the create operations
     *
     * @return array Validation roles to use for updating model
     */
    public function getValidationRulesUpdating()
    {
        return $this->getValidationRules();
    }

    /**
     * Return any custom validation rule messages to be used
     *
     * @return array
     */
    public function getValidationMessages()
    {
        return [];
    }
}
