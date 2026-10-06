<?php

declare(strict_types=1);

namespace Tests\Integrations\Rest;

use Eva\Http\HttpMethodsEnum;
use Eva\Http\Message\Request;
use Tests\Integrations\ApiTestCase;

class ArticleResourceTest extends ApiTestCase
{
    public function testArticleIndex(): void
    {
        $expectedResponse = file_get_contents('./var/tests/responses/Article/IndexByCategory.json');
        $expectedResponse = json_encode(json_decode($expectedResponse));
        $request = new Request(HttpMethodsEnum::GET, '/api/categories/1/articles?per_page=3');
        $response = $this->application->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedResponse, $response->getBody());

        $request = new Request(HttpMethodsEnum::GET, '/api/categories/1/articles?per_page=3&page=2');
        $response = $this->application->handle($request);
        $expectedResponse = file_get_contents('./var/tests/responses/Article/IndexByCategoryPage2.json');
        $expectedResponse = json_encode(json_decode($expectedResponse));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedResponse, $response->getBody());

        $request = new Request(HttpMethodsEnum::GET, '/api/categories/1/articles?per_page=3&sort=views_count&order=desc');
        $response = $this->application->handle($request);
        $expectedResponse = file_get_contents('./var/tests/responses/Article/IndexByCategorySortByViews.json');
        $expectedResponse = json_encode(json_decode($expectedResponse));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedResponse, $response->getBody());

        $request = new Request(HttpMethodsEnum::GET, '/api/categories/1/articles?per_page=3&page=2&sort=views_count&order=desc');
        $response = $this->application->handle($request);
        $expectedResponse = file_get_contents('./var/tests/responses/Article/IndexByCategorySortByViewsPage2.json');
        $expectedResponse = json_encode(json_decode($expectedResponse));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedResponse, $response->getBody());
    }

    public function testArticleShow(): void
    {
        $request = new Request(HttpMethodsEnum::GET, '/api/articles/101');
        $response = $this->application->handle($request);
        $expectedResponse = file_get_contents('./var/tests/responses/Article/Show.json');
        $expectedResponse = json_encode(json_decode($expectedResponse));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedResponse, $response->getBody());
    }
}
