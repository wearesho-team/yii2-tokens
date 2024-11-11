<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Token\Tests\Unit;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Wearesho\Yii2\Token;
use yii\base;
use yii\di\Container;
use yii\redis\Connection;

class ValidationBehaviorTest extends TestCase
{
    protected function setUp(): void
    {
        Carbon::setTestNow(Carbon::make('2020-01-01'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function testInstance(): Token\ValidationBehavior
    {
        $behavior = new Token\ValidationBehavior();
        $this->assertInstanceOf(Token\ValidationBehavior::class, $behavior);

        return $behavior;
    }

    /**
     * @depends testInstance
     */
    public function testGetEvents(Token\ValidationBehavior $behavior): Token\ValidationBehavior
    {
        $this->assertEquals(
            [base\Model::EVENT_BEFORE_VALIDATE => 'beforeValidate'],
            $behavior->events()
        );

        return $behavior;
    }

    /**
     * @depends testGetEvents
     */
    public function testTypeNotAsString(Token\ValidationBehavior $behavior): void
    {
        $this->expectException(base\InvalidConfigException::class);

        $behavior->beforeValidate(new base\ModelEvent());
    }

    public function testFailedValidation(): void
    {
        $model = new Token\Tests\Mocks\Model([
            'hash' => 'hash',
        ]);
        $event = new base\ModelEvent();

        $model->trigger(base\Model::EVENT_BEFORE_VALIDATE, $event);

        $this->assertFalse($event->isValid);
    }

    public function testFailedHexists(): void
    {
        $hash = Uuid::uuid4()->toString();
        \Yii::$container = new Container();
        \Yii::$container->set(
            Token\Repository::class,
            function ($container, $params, $config) use ($hash) {
                $stub = $this->createMock(Connection::class);

                $stub->expects($this->once())
                    ->method('__call')
                    ->with(
                        $this->equalTo('hexists'),
                        $this->equalTo(["y2rt-{$hash}", 'type'])
                    )
                    ->willReturn(0);

                return new Token\Repository([
                    'redis' => $stub
                ]);
            }
        );

        $model = new Token\Tests\Mocks\Model([
            'hash' => $hash
        ]);
        $event = new base\ModelEvent();

        $model->trigger(base\Model::EVENT_BEFORE_VALIDATE, $event);

        $this->assertFalse($event->isValid);
    }

    public function testSuccessHexistsWithIncorrectType(): void
    {
        $hash = Uuid::uuid4()->toString();
        \Yii::$container = new Container();
        \Yii::$container->set(
            Token\Repository::class,
            function ($container, $params, $config) use ($hash) {
                $stub = $this->createMock(Connection::class);

                $map = [
                    [
                        'hexists',
                        ["y2rt-{$hash}", 'type'],
                        1,
                    ],
                    [
                        'hget',
                        ["y2rt-{$hash}", 'type'],
                        'test-type',
                    ],
                    [
                        'hget',
                        ["y2rt-{$hash}", 'owner'],
                        'test-owner',
                    ],
                    [
                        'hget',
                        ["y2rt-{$hash}", 'value'],
                        'test-value',
                    ],
                    [
                        'ttl',
                        ["y2rt-{$hash}"],
                        200
                    ]
                ];
                $stub->expects($this->exactly(5))
                    ->method('__call')
                    ->will($this->returnValueMap($map));

                return new Token\Repository([
                    'redis' => $stub
                ]);
            }
        );

        $model = new Token\Tests\Mocks\Model([
            'hash' => $hash
        ]);
        $event = new base\ModelEvent();

        $model->trigger(base\Model::EVENT_BEFORE_VALIDATE, $event);
        $this->assertFalse($event->isValid);
    }

    public function testInvalidTokenOwner(): void
    {
        $hash = Uuid::uuid4()->toString();
        \Yii::$container = new Container();
        \Yii::$container->set(
            Token\Repository::class,
            function ($container, $params, $config) use ($hash) {
                $stub = $this->createMock(Connection::class);

                $map = [
                    [
                        'hexists',
                        ["y2rt-{$hash}", 'type'],
                        1,
                    ],
                    [
                        'hget',
                        ["y2rt-{$hash}", 'type'],
                        'registration',
                    ],
                    [
                        'hget',
                        ["y2rt-{$hash}", 'owner'],
                        'test-owner',
                    ],
                    [
                        'hget',
                        ["y2rt-{$hash}", 'value'],
                        'test-value',
                    ],
                    [
                        'ttl',
                        ["y2rt-{$hash}"],
                        200
                    ]
                ];
                $stub->expects($this->exactly(5))
                    ->method('__call')
                    ->will($this->returnValueMap($map));

                return new Token\Repository([
                    'redis' => $stub
                ]);
            }
        );

        $model = new Token\Tests\Mocks\Model([
            'hash' => $hash,
        ]);
        $event = new base\ModelEvent();

        $model->trigger(base\Model::EVENT_BEFORE_VALIDATE, $event);
        $this->assertFalse($event->isValid);
        $this->assertEquals(
            [
                'tokenOwner' => ['tokenOwner is invalid.']
            ],
            $model->getErrors()
        );
    }

    public function testInvalidToken(): void
    {
        $hash = Uuid::uuid4()->toString();
        \Yii::$container = new Container();
        \Yii::$container->set(
            Token\Repository::class,
            function ($container, $params, $config) use ($hash) {
                $stub = $this->createMock(Connection::class);

                $map = [
                    [
                        'hexists',
                        ["y2rt-{$hash}", 'type'],
                        1,
                    ],
                    [
                        'hget',
                        ["y2rt-{$hash}", 'type'],
                        'registration',
                    ],
                    [
                        'hget',
                        ["y2rt-{$hash}", 'owner'],
                        'token',
                    ],
                    [
                        'hget',
                        ["y2rt-{$hash}", 'value'],
                        'test-value',
                    ],
                    [
                        'ttl',
                        ["y2rt-{$hash}"],
                        200
                    ]
                ];
                $stub->expects($this->exactly(5))
                    ->method('__call')
                    ->will($this->returnValueMap($map));

                return new Token\Repository([
                    'redis' => $stub
                ]);
            }
        );

        $model = new Token\Tests\Mocks\Model([
            'hash' => $hash,
            'tokenOwner' => 'token'
        ]);
        $event = new base\ModelEvent();

        $model->trigger(base\Model::EVENT_BEFORE_VALIDATE, $event);
        $this->assertFalse($event->isValid);
        $this->assertEquals(
            [
                'token' => ['token is invalid.']
            ],
            $model->getErrors()
        );
    }

    public function testSuccessValidation(): void
    {
        $hash = Uuid::uuid4()->toString();
        \Yii::$container = new Container();
        \Yii::$container->set(
            Token\Repository::class,
            function ($container, $params, $config) use ($hash) {
                $stub = $this->createMock(Connection::class);

                $map = [
                    [
                        'hexists',
                        ["y2rt-{$hash}", 'type'],
                        1,
                    ],
                    [
                        'hget',
                        ["y2rt-{$hash}", 'type'],
                        'registration',
                    ],
                    [
                        'hget',
                        ["y2rt-{$hash}", 'owner'],
                        'owner',
                    ],
                    [
                        'hget',
                        ["y2rt-{$hash}", 'value'],
                        'token',
                    ],
                    [
                        'ttl',
                        ["y2rt-{$hash}"],
                        200
                    ]
                ];
                $stub->expects($this->exactly(5))
                    ->method('__call')
                    ->will($this->returnValueMap($map));

                return new Token\Repository([
                    'redis' => $stub
                ]);
            }
        );

        $model = new Token\Tests\Mocks\Model([
            'hash' => $hash,
            'tokenOwner' => 'owner',
            'token' => 'token'
        ]);
        $event = new base\ModelEvent();

        $model->trigger(base\Model::EVENT_BEFORE_VALIDATE, $event);
        $this->assertTrue($event->isValid);
    }
}
