<?php

namespace Wearesho\Yii2\Token\Tests\Unit;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Wearesho\Yii2\Token\Entity;

/**
 * Class EntityTest
 * @package Wearesho\Yii2\Token\Tests\Unit
 */
class EntityTest extends TestCase
{
    protected const TYPE = 'token';
    protected const OWNER = 'owner';
    protected const EXPIRE = '2020-03-12';
    protected const VALUE = 'value';
    
    /** @var Entity */
    protected $entity;
    
    protected function setUp(): void
    {
        $this->entity = new Entity(
            static::TYPE,
            static::OWNER,
            static::VALUE,
            Carbon::make(static::EXPIRE)
        );
    }

    public function testGetToken(): void
    {
        $this->assertEquals(static::TYPE, $this->entity->getType());
    }

    public function testGetOwner(): void
    {
        $this->assertEquals(static::OWNER, $this->entity->getOwner());
    }

    public function testGetExpire(): void
    {
        $this->assertEquals(
            Carbon::make(static::EXPIRE),
            Carbon::make($this->entity->getExpireAt())
        );
    }

    public function testGetValue(): void
    {
        $this->assertEquals(static::VALUE, $this->entity->getValue());
    }
}
