<?php

declare(strict_types=1);

namespace App\Validator;

use App\Service\Seo\PostSeoResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SeoPatternValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof SeoPattern) {
            throw new UnexpectedTypeException($constraint, SeoPattern::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        foreach (PostSeoResolver::findUnknownPlaceholders((string) $value) as $placeholder) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ placeholder }}', $placeholder)
                ->setParameter('{{ available }}', implode(', ', array_keys(PostSeoResolver::PLACEHOLDERS)))
                ->addViolation();
        }
    }
}
