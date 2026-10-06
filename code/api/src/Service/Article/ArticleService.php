<?php

declare(strict_types=1);

namespace App\Service\Article;

use App\Dto\Common\Pagination;
use App\Dto\Common\Sort;
use App\Exception\Application\BadRequestException;
use App\Exception\Application\NotFoundException;
use App\Repository\Article\ArticleRepositoryInterface;

final class ArticleService
{
    public function __construct(private readonly ArticleRepositoryInterface $articleRepository)
    {
    }

    public function getByCategory(int $categoryId, Pagination $pagination, Sort $sort): array
    {
        if ($pagination->page < 1 || $pagination->perPage < 1) {
            throw new BadRequestException();
        }

        $allowedFields = ['published_at', 'views_count'];
        if (!in_array($sort->field, $allowedFields, true)) {
            throw new BadRequestException();
        }

        $order = strtolower($sort->order);
        if (!in_array($order, ['asc', 'desc'], true)) {
            throw new BadRequestException();
        }

        $result = $this->articleRepository->findByCategory($categoryId, $pagination, new Sort($sort->field, $order));

        $categoryIdsMap = $this->getCategoryIdsMap($result['items'] ?? []);

        foreach ($result['items'] ?? [] as $index => $item) {
            $id = $item['id'];
            $result['items'][$index]['category_ids'] = $categoryIdsMap[$id] ?? [];
        }

        return $result;
    }

    public function getTopByCategoryIds(array $categoryIds, int $limitTopPerCategory, Sort $sort): array
    {
        $allowedFields = ['published_at', 'views_count'];
        if (!in_array($sort->field, $allowedFields, true)) {
            throw new BadRequestException();
        }

        if ($limitTopPerCategory < 1) {
            throw new BadRequestException();
        }

        $order = strtolower($sort->order);
        if (!in_array($order, ['asc', 'desc'], true)) {
            throw new BadRequestException();
        }

        $result = [];
        $normalizedSort = new Sort($sort->field, $order);

        foreach ($categoryIds as $categoryId) {
            $page = new Pagination(1, $limitTopPerCategory);
            $categoryArticles = $this->articleRepository->findByCategory($categoryId, $page, $normalizedSort);
            $items = $categoryArticles['items'] ?? [];
            $categoryIdsMap = $this->getCategoryIdsMap($items);

            foreach ($items as $index => $item) {
                $items[$index]['category_ids'] = $categoryIdsMap[$item['id']] ?? [];
            }

            $result[$categoryId] = $items;
        }

        return $result;
    }

    public function getById(int $id): array
    {
        $article = $this->articleRepository->findById($id);
        if (null === $article) {
            throw new NotFoundException();
        }

        $categoryIdsMap = $this->articleRepository->findCategoryIdsByArticleIds([$id]);
        $article['category_ids'] = $categoryIdsMap[$id] ?? [];

        return $article;
    }

    public function getRelatedByArticleId(int $id): array
    {
        $relatedArticles = $this->articleRepository->findRelatedByArticleId($id);

        $relatedCategoryIdsMap = $this->getCategoryIdsMap($relatedArticles);

        foreach ($relatedArticles as $index => $relatedArticle) {
            $relatedArticles[$index]['category_ids'] = $relatedCategoryIdsMap[$relatedArticle['id']] ?? [];
        }

        return $relatedArticles;
    }

    private function getCategoryIdsMap(array $items): array
    {
        $articleIds = array_map(static fn (array $item): int => $item['id'], $items);

        return [] !== $articleIds
            ? $this->articleRepository->findCategoryIdsByArticleIds($articleIds)
            : [];
    }
}
