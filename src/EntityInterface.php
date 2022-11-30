<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Token;

interface EntityInterface
{
    public function getType(): string;

    public function getOwner(): string;

    public function getValue(): string;

    public function getExpireAt(): \DateTimeInterface;
}
