# Yii2 Tokens

Storing tokens (authorization, registration) in redis database.

## Installation
```bash
composer require wearesho-team/yii2-tokens:^1.0.0
```

## Usage

### Repository
To store tokens you should use [Repository](./src/Repository.php) that will receive
[token entity](./src/EntityInterface.php) and generate UUIDv4 hash.
Then, you can receive token using generated hash.

```php
<?php

use Wearesho\Yii2\Token;

$repository = new Token\Repository([
    'redis' => 'redis', // your yii2-redis connection
]);

$token = new Token\Entity(
    $type = 'registration',
    $tokenOwner = 'foo@bar.com',
    $value = 'random-token',
    (new \DateTime())->add('PT30M') // expire after 30 minutes
);

$hash = $repository->put($token);

// If you want to receive token

$token = $repository->get($hash); // entity or null

```

### ValidationBehavior
To validate model attributes, that contains hash, owner and value you should use
[validation behavior](./src/ValidationBehavior.php), instead of validators because three attributes have to be validated
one-time.
`{attribute} is invalid.` message will be add to attribute errors and validation will fail (beforeValidate event).

```php
<?php

use yii\base;
use Wearesho\Yii2\Token;

class Model extends base\Model {
    /** @var string */
    public $hash;
    
    /** @var string */
    public $tokenOwner; // or `phone`, `email` etc.
    
    /** @var string */
    public $token;
    
    public function behaviors(): array
    {
        return [
            'validateToken' => [
                'class' => Token\ValidationBehavior::class,
                'type' => 'registration',
            ],    
        ];
    }
    
    public function rules(): array
    {
        return [
            [['hash', 'tokenOwner', 'token',], 'safe',], // to load using $model->load    
        ];
    }
}

$repository = new Token\Repository;

$model = new Model;
$model->hash = 'some-invalid-hash';
$model->owner = 'foo@bar.com';
$model->token = 'some-random-token';

$model->validate(); // false

$hash = $repository->put(new Token\Entity(
    'registration',
    $model->tokenWwner,
    $model->token,
    (new \DateTime)->add(new \DateInterval('PT1M'))
));

$model->hash = $hash;

$model->validate(); // true
```

## Contributors
- [Alexander Letnikow](mailto:reclamme@gmail.com)

## License
[MIT](./LICENSE)
