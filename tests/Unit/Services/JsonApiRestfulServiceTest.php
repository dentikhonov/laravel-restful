<?php

namespace Devolt\Restful\Tests\Unit\Services;

use Devolt\Restful\Models\Model;
use Devolt\Restful\Services\JsonApiRestfulService;
use Devolt\Restful\Tests\TestModels\TestModel;
use Illuminate\Http\Request;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class JsonApiRestfulServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function it_can_get_per_page_from_request()
    {
        $expected = 24;

        $jsonService = new JsonApiRestfulService(new Request([
            'per_page' => $expected,
        ]));
        $jsonService->setModel(TestModel::class);

        $this->assertEquals($expected, $jsonService->getPerPage());
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function it_uses_per_page_from_model_as_fallback()
    {
        $expected = 24;

        $model = Mockery::mock(TestModel::class);
        $model->shouldReceive('getPerPage')->andReturn($expected);

        $jsonService = Mockery::mock(JsonApiRestfulService::class, [new Request()])->makePartial();
        $jsonService->shouldReceive('getModelInstance')->andReturn($model);

        $this->assertEquals($expected, $jsonService->getPerPage());
    }
}
