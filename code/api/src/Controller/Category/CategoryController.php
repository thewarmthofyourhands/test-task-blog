<?php

declare(strict_types=1);

namespace App\Controller\Category;

use App\Infrastructure\Rest\ApiResponse;
use App\UseCase\Category\GetCategoryIndexUseCase;
use App\UseCase\Category\GetCategoryShowUseCase;
use Eva\Http\Message\Request;
use Eva\Http\Message\ResponseInterface;

final class CategoryController
{
    public function __construct(
        private readonly GetCategoryIndexUseCase $getCategoryIndexUseCase,
        private readonly GetCategoryShowUseCase $getCategoryShowUseCase,
    ) {
    }

    public function index(Request $request): ResponseInterface
    {
        $data = $this->getCategoryIndexUseCase->execute();

        return (new ApiResponse($data))->build();
    }

    public function show(Request $request, string $id): ResponseInterface
    {
        $id = (int) $id;
        $data = $this->getCategoryShowUseCase->execute($id);

        return (new ApiResponse($data))->build();
    }
}
