<?php


namespace Devolt\Restful\Models\Traits;

use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;


/**
 * @mixin \Eloquent
 */
trait WithQueryBuilder
{

    /**
     * @return string[]|AllowedFilter[]|mixed[]|array
     */
    public function getAllowedFilters(): array
    {
        return [];
    }

    /**
     * @return string|string[]|AllowedSort|AllowedSort[]|array|null
     */
    public function getDefaultSorts()
    {
        return '-updated_at';
    }

    /**
     * @return string[]|AllowedSort[]|mixed[]|array
     */
    public function getAllowedSorts(): array
    {
        return [];
    }

    /**
     * @return string[]|AllowedInclude[]|mixed[]|array
     */
    public function getAllowedIncludes(): array
    {
        return [];
    }

    /**
     * @return string[]|mixed[]|array
     */
    public function getAllowedFields(): array
    {
        return [];
    }

    /**
     * @return string[]|mixed[]|array
     */
    public function getAllowedAppends(): array
    {
        return [];
    }
}
