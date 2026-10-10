<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class PostViewEvent extends Event
{
    public function __construct(
        public string $postSlug,
    ) {
    }
}
