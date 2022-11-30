<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Token;

use yii\base;
use yii\di;
use yii\redis;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class Repository extends base\BaseObject
{
    protected const FIELD_TYPE = 'type';
    protected const FIELD_OWNER = 'owner';
    protected const FIELD_VALUE = 'value';

    /** @var string|array|redis\Connection */
    public $redis = 'redis';

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->redis = di\Instance::ensure($this->redis, redis\Connection::class);
    }

    public function put(EntityInterface $token): string
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $hash = Uuid::uuid4()->toString();
        $key = $this->getKey($hash);
        $this->redis->multi();

        $this->redis->hset($key, static::FIELD_TYPE, $token->getType());
        $this->redis->hset($key, static::FIELD_OWNER, $token->getOwner());
        $this->redis->hset($key, static::FIELD_VALUE, $token->getValue());

        $expire = Carbon::now()->diffInSeconds($token->getExpireAt());
        $this->redis->expire($key, $expire);

        $this->redis->exec();

        return $hash;
    }

    public function get(string $hash): ?EntityInterface
    {
        if (!$this->validate($hash)) {
            return null;
        }

        $key = $this->getKey($hash);
        if (!$this->redis->hexists($key, static::FIELD_TYPE)) {
            return null;
        }

        $type = $this->redis->hget($key, static::FIELD_TYPE);
        $owner = $this->redis->hget($key, static::FIELD_OWNER);
        $value = $this->redis->hget($key, static::FIELD_VALUE);
        $expireAt = Carbon::now()->addSeconds($this->redis->ttl($key));

        return new Entity($type, $owner, $value, $expireAt);
    }

    protected function getKey(string $hash): string
    {
        return "y2rt-{$hash}";
    }

    protected function validate(string $hash): bool
    {
        return Uuid::isValid($hash);
    }
}
