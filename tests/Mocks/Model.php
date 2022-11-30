<?php

namespace Wearesho\Yii2\Token\Tests\Mocks;

use Wearesho\Yii2\Token;

use yii\base;

/**
 * Class Model
 * @package Wearesho\Yii2\Token\Tests\Mocks
 */
class Model extends base\Model
{
    public ?string $hash = null;

    public ?string $tokenOwner = null;

    public ?string $token = null;

    public function behaviors(): array
    {
        return [
            'validateToken' => [
                'class' => Token\ValidationBehavior::class,
                'type' => 'registration',
                'tokenOwner' => 'tokenOwner'
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['hash', 'tokenOwner', 'token',], 'safe',],
        ];
    }
}
