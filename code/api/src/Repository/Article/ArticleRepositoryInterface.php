<?php

declare(strict_types=1);

namespace App\Repository\Article;

use App\Dto\Common\Pagination;
use App\Dto\Common\Sort;

interface ArticleRepositoryInterface
{
    public function findByCategory(int $categoryId, Pagination $pagination, Sort $sort): array;

    public function findById(int $id): null|array;

    public function findRelatedByArticleId(int $articleId, int $limit = 3): array;

    public function findCategoryIdsByArticleIds(array $articleIds): array;
}
