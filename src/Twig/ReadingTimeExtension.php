<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Attribute\AsTwigFilter;

final class ReadingTimeExtension
{
    private const int WORDS_PER_MINUTE = 200;

    #[AsTwigFilter('reading_time')]
    public function readingTime(?string $markdown): int
    {
        $text = preg_replace('/^(```|~~~).*?^\1/ms', ' ', (string) $markdown) ?? '';
        $words = preg_match_all('/[\p{L}\p{N}]+/u', $text);

        return max(1, (int) ceil($words / self::WORDS_PER_MINUTE));
    }
}
