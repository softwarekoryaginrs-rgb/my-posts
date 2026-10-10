<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class PostSeo
{
    public function __construct(
        public string $title,
        public string $description,
    ) {
    }
}
