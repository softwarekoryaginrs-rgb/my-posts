<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Post;
use App\DTO\PostSeo;
use App\Resolver\PostSeoResolver;
use Twig\Attribute\AsTwigFunction;

final class PostSeoExtension
{
    public function __construct(
        private readonly PostSeoResolver $resolver,
    ) {
    }

    #[AsTwigFunction('post_seo')]
    public function postSeo(Post $post): PostSeo
    {
        return $this->resolver->resolve($post);
    }
}
