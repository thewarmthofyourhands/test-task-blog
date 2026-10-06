<?php

declare(strict_types=1);

namespace Tests\Integrations\Seed;

use App\Manager\RedisManager;
use Eva\Database\ConnectionStoreInterface;

class MainTestSeed
{
    public static function init(ConnectionStoreInterface $connectionStore): void
    {
        try {
            $connection = $connectionStore->get();
            $connection->execute('TRUNCATE TABLE article_category');
            $connection->execute('TRUNCATE TABLE articles');
            $connection->execute('TRUNCATE TABLE categories');
            $connection->beginTransaction();
            $categories = [
                [
                    'id' => 1,
                    'name' => 'Technology',
                    'description' => 'Articles about technology and software development.',
                ],
                [
                    'id' => 2,
                    'name' => 'Science',
                    'description' => 'Articles about science and research.',
                ],
                [
                    'id' => 3,
                    'name' => 'Backend',
                    'description' => 'Articles about backend.',
                ],
            ];

            $stmt = $connection->prepare(<<<'SQL'
                INSERT INTO categories (
                    id,
                    name,
                    description,
                    created_at,
                    updated_at
                ) VALUES (
                    :id,
                    :name,
                    :description,
                    :created_at,
                    :updated_at
                )
            SQL);

            foreach ($categories as $category) {
                $stmt->execute([
                    'id' => $category['id'],
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'created_at' => '2026-09-01 12:00:00',
                    'updated_at' => '2026-09-01 12:00:00',
                ]);
            }

            $stmt->closeCursor();

            $articles = [
                [
                    'id' => 101,
                    'title' => 'Building Scalable APIs',
                    'image' => 'https://picsum.photos/600/300',
                    'description' => 'How to design APIs for high-load systems.',
                    'content' => 'Full article content goes here. This is an example of the complete article text.',
                    'views_count' => 5200,
                    'published_at' => '2026-09-20 12:00:00',
                ],
                [
                    'id' => 102,
                    'title' => 'MySQL Indexing',
                    'image' => 'https://picsum.photos/600/300',
                    'description' => 'Practical guide to database indexes.',
                    'content' => 'Practical guide to database indexes.',
                    'views_count' => 15420,
                    'published_at' => '2026-09-18 10:00:00',
                ],
                [
                    'id' => 103,
                    'title' => 'Caching Strategies',
                    'image' => 'https://picsum.photos/600/300',
                    'description' => 'Caching approaches for high-load applications.',
                    'content' => 'Caching approaches for high-load applications.',
                    'views_count' => 8700,
                    'published_at' => '2026-09-15 14:00:00',
                ],
                [
                    'id' => 104,
                    'title' => 'Database Optimization',
                    'image' => 'https://picsum.photos/600/300',
                    'description' => 'Optimizing databases for growing workloads.',
                    'content' => 'Optimizing databases for growing workloads.',
                    'views_count' => 12600,
                    'published_at' => '2026-09-12 09:00:00',
                ],
                [
                    'id' => 105,
                    'title' => 'Message Queues',
                    'image' => 'https://picsum.photos/600/300',
                    'description' => 'Using asynchronous processing in modern applications.',
                    'content' => 'Using asynchronous processing in modern applications.',
                    'views_count' => 6800,
                    'published_at' => '2026-09-10 16:00:00',
                ],
                [
                    'id' => 106,
                    'title' => 'Distributed Systems',
                    'image' => 'https://picsum.photos/600/300',
                    'description' => 'Fundamentals of building distributed applications.',
                    'content' => 'Fundamentals of building distributed applications.',
                    'views_count' => 4200,
                    'published_at' => '2026-09-08 13:00:00',
                ],
                [
                    'id' => 201,
                    'title' => 'The Future of Space Exploration',
                    'image' => 'https://picsum.photos/600/300',
                    'description' => 'What comes next in space exploration.',
                    'content' => 'Article Text.',
                    'views_count' => 4100,
                    'published_at' => '2026-09-19 08:00:00',
                ],
                [
                    'id' => 202,
                    'title' => 'Understanding Black Holes',
                    'image' => 'https://picsum.photos/600/300',
                    'description' => 'An introduction to black holes and modern astrophysics.',
                    'content' => 'Article Text.',
                    'views_count' => 11200,
                    'published_at' => '2026-09-17 11:00:00',
                ],
            ];

            $stmt = $connection->prepare(<<<'SQL'
                INSERT INTO articles (
                    id,
                    title,
                    image,
                    description,
                    content,
                    views_count,
                    published_at,
                    created_at,
                    updated_at
                ) VALUES (
                    :id,
                    :title,
                    :image,
                    :description,
                    :content,
                    :views_count,
                    :published_at,
                    :created_at,
                    :updated_at
                )
            SQL);

            foreach ($articles as $article) {
                $stmt->execute([
                    'id' => $article['id'],
                    'title' => $article['title'],
                    'image' => $article['image'],
                    'description' => $article['description'],
                    'content' => $article['content'],
                    'views_count' => $article['views_count'],
                    'published_at' => $article['published_at'],
                    'created_at' => $article['published_at'],
                    'updated_at' => $article['published_at'],
                ]);
            }

            $stmt->closeCursor();

            $articleCategories = [
                [101, 1],
                [101, 3],

                [102, 1],
                [102, 3],

                [103, 1],
                [103, 3],

                [104, 1],
                [104, 3],

                [105, 1],

                [106, 1],

                [201, 2],
                [202, 2],
            ];

            $stmt = $connection->prepare(<<<'SQL'
                INSERT INTO article_category (
                    article_id,
                    category_id
                ) VALUES (
                    :article_id,
                    :category_id
                )
            SQL);

            foreach ($articleCategories as [$articleId, $categoryId]) {
                $stmt->execute([
                    'article_id' => $articleId,
                    'category_id' => $categoryId,
                ]);
            }

            $stmt->closeCursor();

            $connection->commit();
        } catch (\Throwable $e) {
            if (isset($connection) && $connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $e;
        }
    }
}
