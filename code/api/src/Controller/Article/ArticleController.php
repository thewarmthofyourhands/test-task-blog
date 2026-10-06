<?php

declare(strict_types=1);

namespace App\Controller\Article;

use App\Dto\Common\Sort;
use App\Infrastructure\Rest\ApiResponse;
use App\UseCase\Article\GetArticlesByCategoryUseCase;
use App\UseCase\Article\GetArticleShowUseCase;
use Eva\Http\Message\Request;
use Eva\Http\Message\ResponseInterface;
use Eva\Http\Parser\JsonRequestParser;

final class ArticleController
{
    public function __construct(
        private readonly GetArticlesByCategoryUseCase $getArticlesByCategoryUseCase,
        private readonly GetArticleShowUseCase $getArticleShowUseCase,
    ) {
    }

    public function indexByCategory(Request $request, string $id): ResponseInterface
    {
        $categoryId = (int) $id;
        $query = JsonRequestParser::parseParams($request);

        $page = isset($query['page']) ? (int) $query['page'] : 1;
        $perPage = isset($query['per_page']) ? (int) $query['per_page'] : 10;
        $field = isset($query['sort']) ? (string) $query['sort'] : 'published_at';
        $order = isset($query['order']) ? (string) $query['order'] : 'desc';

        $sort = new Sort($field, $order);
        $data = $this->getArticlesByCategoryUseCase->execute($categoryId, $page, $perPage, $sort);

        return (new ApiResponse($data))->build();
    }

    public function show(Request $request, string $id): ResponseInterface
    {
        $articleId = (int) $id;
        $data = $this->getArticleShowUseCase->execute($articleId);

        return (new ApiResponse($data))->build();
    }
}
