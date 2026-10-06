<?php

declare(strict_types=1);

namespace App\Repository\Article;

use App\Dto\Common\Pagination;
use App\Dto\Common\Sort;
use Eva\Database\ConnectionStoreInterface;
use PDO;

final readonly class ArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(private ConnectionStoreInterface $connectionStore)
    {
    }

    public function findByCategory(int $categoryId, Pagination $pagination, Sort $sort): array
    {
        $connection = $this->connectionStore->get();

        $fieldMap = [
            'published_at' => 'a.published_at',
            'views_count' => 'a.views_count',
        ];
        $orderBy = $fieldMap[$sort->field] ?? $fieldMap['published_at'];
        $order = strtoupper($sort->order);

        $countStmt = $connection->prepare(<<<SQL
            select count(*) as cnt
            from articles a
            inner join article_category ac on ac.article_id = a.id
            where ac.category_id = :category_id
            SQL,
            ['category_id' => $categoryId],
        );
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        $countStmt->closeCursor();

        $offset = ($pagination->page - 1) * $pagination->perPage;

        $sql = sprintf(
            'select a.id, a.image, a.title, a.description, a.published_at, a.views_count
            from articles a
            inner join article_category ac on ac.article_id = a.id
            where ac.category_id = :category_id
            order by %s %s, a.id asc
            limit %d offset %d',
            $orderBy,
            $order,
            $pagination->perPage,
            $offset,
        );
        $stmt = $connection->prepare($sql, ['category_id' => $categoryId]);
        $stmt->execute();

        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $publishedAt = new \DateTimeImmutable($row['published_at']);
            $items[] = [
                'id' => $row['id'],
                'image' => $row['image'],
                'title' => $row['title'],
                'description' => $row['description'],
                'published_at' => $publishedAt->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'),
                'views_count' => $row['views_count'],
            ];
        }

        $stmt->closeCursor();
        $lastPage = $pagination->perPage > 0 ? (int) ceil($total / $pagination->perPage) : 1;

        return [
            'items' => $items,
            'pagination' => [
                'page' => $pagination->page,
                'per_page' => $pagination->perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
            'sort' => [
                'field' => $sort->field,
                'order' => strtolower($sort->order),
            ],
        ];
    }

    public function findById(int $id): null|array
    {
        $connection = $this->connectionStore->get();
        $stmt = $connection->prepare(<<<SQL
            select id, image, title, description, content, published_at, views_count
            from articles
            where id = :id
            SQL,
            ['id' => $id],
        );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if (false === $row) {
            return null;
        }

        $publishedAt = new \DateTimeImmutable($row['published_at']);

        return [
            'id' => $row['id'],
            'image' => $row['image'],
            'title' => $row['title'],
            'description' => $row['description'],
            'content' => $row['content'],
            'published_at' => $publishedAt->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'),
            'views_count' => $row['views_count'],
        ];
    }

    public function findRelatedByArticleId(int $articleId, int $limit = 3): array
    {
        $connection = $this->connectionStore->get();
        $stmt = $connection->prepare(<<<SQL
            select distinct a.id, a.image, a.title, a.description, a.published_at, a.views_count
            from articles a
            inner join article_category ac on ac.article_id = a.id
            where ac.category_id in (
                select category_id
                from article_category
                where article_id = :article_id
            )
              and a.id <> :article_id
            order by a.published_at desc, a.id asc
            limit $limit
            SQL,
            ['article_id' => $articleId],
        );
        $stmt->execute();

        $related = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = (int) $row['id'];
            $publishedAt = new \DateTimeImmutable($row['published_at']);
            $related[] = [
                'id' => $id,
                'image' => $row['image'],
                'title' => $row['title'],
                'description' => $row['description'],
                'published_at' => $publishedAt->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'),
                'views_count' => $row['views_count'],
            ];
        }

        $stmt->closeCursor();

        return $related;
    }

    public function findCategoryIdsByArticleIds(array $articleIds): array
    {
        $result = [];
        if ([] === $articleIds) {
            return $result;
        }

        $connection = $this->connectionStore->get();

        $placeholders = [];
        $params = [];
        foreach (array_values($articleIds) as $index => $articleId) {
            $placeholder = ':article_id_' . $index;
            $placeholders[] = $placeholder;
            $params['article_id_' . $index] = (int) $articleId;
        }

        $sql = sprintf(
            'select article_id, category_id
            from article_category
            where article_id in (%s)
            order by article_id asc, category_id asc',
            implode(', ', $placeholders),
        );

        $stmt = $connection->prepare($sql, $params);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $articleId = (int) $row['article_id'];
            $result[$articleId] ??= [];
            $result[$articleId][] = $row['category_id'];
        }

        $stmt->closeCursor();

        return $result;
    }
}
