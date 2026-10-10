<?php

namespace App\Tests\Twig;

use App\Twig\ReadingTimeExtension;
use PHPUnit\Framework\TestCase;

class ReadingTimeExtensionTest extends TestCase
{
    public function testShortTextTakesAtLeastOneMinute(): void
    {
        self::assertSame(1, (new ReadingTimeExtension())->readingTime('Привет, мир'));
        self::assertSame(1, (new ReadingTimeExtension())->readingTime(null));
    }

    public function testCountsWordsAndIgnoresFencedCode(): void
    {
        $prose = str_repeat('слово ', 401)."\n\n";
        $code = "```php\n".str_repeat('$code = 1; ', 1000)."\n```\n";

        self::assertSame(3, (new ReadingTimeExtension())->readingTime($prose.$code));
    }
}
