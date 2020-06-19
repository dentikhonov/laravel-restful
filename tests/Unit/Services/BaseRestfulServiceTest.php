<?php

namespace Devolt\Restful\Tests\Unit\Services;

use Devolt\Restful\Models\Model;
use Devolt\Restful\Services\BaseRestfulService;
use Devolt\Restful\Services\JsonApiRestfulService;
use Devolt\Restful\Tests\AppTestCase;
use Devolt\Restful\Tests\TestModels\TestModel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class BaseRestfulServiceTest extends AppTestCase
{
    private static array $validationRules = [
        'title' => 'required|string',
        'src' => 'required|url',
    ];

    private static array $input = [
        'title' => 'Ember Hamster',
        'src' => 'http://example.com/images/productivity.png',
        'not_existent' => 'some string, that will be ignored',
    ];

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function it_uses_validation_rules_from_model()
    {
        $model = Mockery::mock(TestModel::class)->makePartial();
        $model->shouldReceive('getValidationRules')->andReturn(self::$validationRules);

        $jsonService = Mockery::mock(BaseRestfulService::class)->makePartial();
        $jsonService->shouldReceive('getModelInstance')->andReturn($model);

        /** @var BaseRestfulService|MockInterface $jsonService */
        $validated = $jsonService->validateResource(self::$input);

        $this->assertArrayHasKey('title', $validated);
        $this->assertArrayHasKey('src', $validated);
        $this->assertArrayNotHasKey('not_existent', $validated);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function it_validates_model_itself_if_no_data_provided()
    {
        /** @var TestModel|MockInterface $model */
        $model = Mockery::mock(TestModel::class)->makePartial();
        $model->shouldReceive('getValidationRules')->andReturn(self::$validationRules);
        $model->fillable(array_keys(self::$input));
        $model->fill(self::$input);

        /** @var BaseRestfulService|MockInterface $jsonService */
        $jsonService = Mockery::mock(BaseRestfulService::class)->makePartial();
        $jsonService->shouldReceive('getModelInstance')->andReturn($model);

        $validated = $jsonService->validateResource($model);

        $this->assertArrayHasKey('title', $validated);
        $this->assertArrayHasKey('src', $validated);
        $this->assertArrayNotHasKey('not_existent', $validated);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function it_should_fail_if_model_not_completely_filled()
    {
        /** @var TestModel|MockInterface $model */
        $model = Mockery::mock(TestModel::class)->makePartial();
        $model->shouldReceive('getValidationRules')->andReturn(self::$validationRules);
        $model->fillable(array_keys(self::$input));
        $model->fill([]);

        /** @var BaseRestfulService|MockInterface $jsonService */
        $jsonService = Mockery::mock(BaseRestfulService::class)->makePartial();
        $jsonService->shouldReceive('getModelInstance')->andReturn($model);

        $this->expectException(ValidationException::class);
        $validated = $jsonService->validateResource($model);
    }
}
