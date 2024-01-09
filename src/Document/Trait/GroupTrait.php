<?php

declare(strict_types=1);

namespace App\Document\Trait;

use Document\Card;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait GroupTrait
{
    #[Assert\Choice(
        choices: self::ALLOWED_GROUPS,
        message: Card::VALIDATION_ERR_ENUM,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field]
    protected string $group = '';

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): Card
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getFields(): array
    {
        $fields = Card::getFields();
        $fields['enum']['group'] = self::ALLOWED_GROUPS;
        
        return $fields;
    }
}
