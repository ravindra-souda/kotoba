<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Doctrine\Odm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Odm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\FetchDeckByCode;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups; 
use Symfony\Component\Validator\Constraints as Assert;

abstract class Card extends AbstractKotobaDocument
{
    public const VALIDATION_ERR_EMPTY =
        'cannot be left empty';
    
    public const VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA =
        'either hiragana or katakana must be filled';

    public const VALIDATION_ERR_MAXLENGTH =
        'cannot not be longer than {{ limit }} characters';

    public const VALIDATION_ERR_NOT_AN_ARRAY =
        'must be a valid array';

    public const VALIDATION_ERR_ENUM =
        'must be one of these: {{ choices }}';

    public const VALIDATION_ERR_JLPT =
        'must be an integer between 1 and 5';

    /** Slugified by the API from reference field (romaji or kanji) */
    #[ApiProperty(identifier: true)]
    #[Groups('read')]
    #[MongoDB\Field(type: 'string')]
    protected string $code = '';

    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: self::VALIDATION_ERR_JLPT,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'int')]
    protected ?int $jlpt = 5;

    /** set by MongoDB */
    #[Groups('read')]
    #[MongoDB\Field(type: 'date_immutable')]
    protected ?\DateTimeImmutable $createdAt = null;

    /** set by MongoDB */
    #[Groups('read')]
    #[MongoDB\Field(type: 'date_immutable')]
    protected ?\DateTimeImmutable $updatedAt = null;

    #[ApiProperty(identifier: false)]
    #[Groups('read')]
    #[MongoDB\Id(strategy: 'AUTO', type: 'object_id')]
    protected string $id;

    #[MongoDB\Field(type: 'int')]
    protected int $increment;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getIncrement(): int
    {
        return $this->increment;
    }

    public function getJlpt(): ?int
    {
        return $this->jlpt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // called right before persist, see App\State\SaveProcessor
    public function finalizeTasks(): static
    {
        return $this;
    }

    public function setCode(string $code): Card
    {
        $this->code = $code;

        return $this;
    }

    // see App\EventListener\PrePersistListener
    public function setCreatedAt(\DateTimeImmutable $date): Card
    {
        $this->createdAt = $date;

        return $this;
    }

    public function setId(string $id): Card
    {
        $this->id = $id;

        return $this;
    }

    public function setJlpt(?int $jlpt): Card
    {
        $this->jlpt = $jlpt;

        return $this;
    }

    // see App\EventListener\PrePersistListener
    public function setIncrement(int $increment): Card
    {
        $this->increment = $increment;

        return $this;
    }

    // see App\EventListener\PreUpdateListener
    public function setUpdatedAt(\DateTimeImmutable $date): Card
    {
        $this->updatedAt = $date;

        return $this;
    }
}
