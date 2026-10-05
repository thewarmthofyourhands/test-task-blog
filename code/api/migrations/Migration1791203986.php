<?php

declare(strict_types=1);

namespace Migrations;

use Eva\Database\Migrations\AbstractMigration;

class Migration1791203986 extends AbstractMigration
{
    public function up(): void
    {
        $sql = <<<SQL
        create table categories (
            id bigint auto_increment primary key,
            name varchar(255) not null,
            description text not null,
            created_at datetime not null,
            updated_at datetime not null
        );

        create table articles (
            id bigint auto_increment primary key,
            title varchar(255) not null,
            image varchar(2048) not null,
            description text not null,
            content text not null,
            views_count bigint unsigned not null default 0,
            published_at datetime not null,
            created_at datetime not null,
            updated_at datetime not null,
            index idx_articles_published_at (published_at),
            index idx_articles_views_count (views_count)
        );

        create table article_category (
            article_id bigint not null,
            category_id bigint not null,
            primary key (article_id, category_id),
            index idx_article_category_category_id_article_id (category_id, article_id)
        );
        SQL;

        $this->execute($sql);
    }

    public function down(): void
    {
        $sql = <<<SQL
        drop table article_category;
        drop table articles;
        drop table categories;
        SQL;

        $this->execute($sql);
    }
}
