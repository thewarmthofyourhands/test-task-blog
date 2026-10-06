<?php

declare(strict_types=1);

namespace App\Dto\Common;

final readonly class Sort
{
    public function __construct(
        public string $field,
        public string $order,
    ) {
    }
}
