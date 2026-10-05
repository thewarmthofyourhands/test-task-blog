<?php

declare(strict_types=1);

namespace Tests\Integrations\Rest;

use Eva\Http\HttpMethodsEnum;
use Eva\Http\Message\Request;
use Tests\Integrations\ApiTestCase;

class CategoryResourceTest extends ApiTestCase
{
    public function testCategoryIndex(): void
    {
        $request = new Request(HttpMethodsEnum::GET, '/api/categories');
        $response = $this->application->handle($request);
        $expectedResponse = file_get_contents('./var/tests/responses/Category/Index.json');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedResponse, $response->getBody());
    }

    public function testCategoryShow(): void
    {
        $request = new Request(HttpMethodsEnum::GET, '/api/categories/1');
        $response = $this->application->handle($request);
        $expectedResponse = file_get_contents('./var/tests/responses/Category/Show.json');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedResponse, $response->getBody());
    }
}
