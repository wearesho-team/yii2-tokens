<?php

namespace Wearesho\Yii2\Token;

/**
 * Class Entity
 * @package Wearesho\Yii2\Token
 */
class Entity implements EntityInterface
{
    use EntityTrait;

    public function __construct(string $type, string $owner, string $value, \DateTimeInterface $expireAt)
    {
        $this->type = $type;
        $this->owner = $owner;
        $this->value = $value;
        $this->expireAt = $expireAt;
    }
}
