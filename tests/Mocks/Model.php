<?php

namespace Wearesho\Yii2\Token\Tests\Mocks;

use Wearesho\Yii2\Token;

/**
 * Class Model
 * @package Wearesho\Yii2\Token\Tests\Mocks
 */
class Model extends \yii\base\Model
{
    /** @var string */
    public $hash;

    /** @var string */
    public $tokenOwner;

    /** @var string */
    public $token;

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
