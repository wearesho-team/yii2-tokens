<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Token;

/**
 * Trait EntityTrait
 * @package Wearesho\Yii2\Token
 *
 * @see EntityInterface implementation
 */
trait EntityTrait
{
    /** @var string */
    protected $type;

    /** @var string */
    protected $owner;

    /** @var string */
    protected $value;

    /** @var \DateTimeInterface */
    protected $expireAt;

    public function getType(): string
    {
        return $this->type;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getExpireAt(): \DateTimeInterface
    {
        return $this->expireAt;
    }
}
