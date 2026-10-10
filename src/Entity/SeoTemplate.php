<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SeoTemplateRepository;
use App\Validator\SeoPattern;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Monolog\DateTimeImmutable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Reusable title/description patterns for post pages; see PostSeoResolver::PLACEHOLDERS for the variables.
 */
#[ORM\Entity(repositoryClass: SeoTemplateRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('name')]
class SeoTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[SeoPattern]
    private ?string $titlePattern = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[SeoPattern]
    private ?string $descriptionPattern = null;

    /**
     * Used for posts that don't pick a template; at most one template is the default.
     */
    #[ORM\Column(options: ['default' => false])]
    private bool $isDefault = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    #[ORM\PreUpdate]
    public function touchUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTitlePattern(): ?string
    {
        return $this->titlePattern;
    }

    public function setTitlePattern(string $titlePattern): self
    {
        $this->titlePattern = $titlePattern;

        return $this;
    }

    public function getDescriptionPattern(): ?string
    {
        return $this->descriptionPattern;
    }

    public function setDescriptionPattern(string $descriptionPattern): self
    {
        $this->descriptionPattern = $descriptionPattern;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
