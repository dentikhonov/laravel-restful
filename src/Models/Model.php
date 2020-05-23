<?php

namespace Devolt\Restful\Models;

use Devolt\Restful\Models\Traits\WithIncludes;
use Devolt\Restful\Models\Traits\WithValidation;
use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    use WithValidation,
        WithIncludes;
}
