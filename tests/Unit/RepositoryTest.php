<?php

namespace Wearesho\Yii2\Token\Tests\Unit;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use yii\redis\Connection;
use yii\di\Container;
use Wearesho\Yii2\Token;
use Ramsey\Uuid\Uuid;

/**
 * Class RepositoryTest
 * @package Wearesho\Yii2\Token\Tests\Unit
 */
class RepositoryTest extends TestCase
{
    public function testPut(): void
    {
        Carbon::setTestNow(Carbon::make('2010-01-01'));
        $expireAt = Carbon::make('2020-01-01');
        $expireDiff = Carbon::getTestNow()->diffInSeconds($expireAt);
        $hash = Uuid::uuid4();
        \Yii::$container = new Container();
        \Yii::$container->set(
            Token\Repository::class,
            function ($container, $params, $config) use ($hash, $expireDiff) {
                $stub = $this->createMock(Connection::class);

                $map = [
                    [
                        'multi',
                        null,
                    ],
                    [
                        'hset',
                        ["y2rt-{$hash}", 'type'],
                    ],
                    [
                        'hset',
                        ["y2rt-{$hash}", 'owner'],
                    ],
                    [
                        'hset',
                        ["y2rt-{$hash}", 'value'],
                    ],
                    [
                        'expire',
                        ["y2rt-{$hash}", $expireDiff],
                    ],
                    [
                        'exec',
                        null
                    ]
                ];
                $stub->expects($this->exactly(6))
                    ->method('__call')
                    ->will($this->returnValueMap($map));

                return new Token\Repository([
                    'redis' => $stub
                ]);
            }
        );

        /** @var Token\Repository $repository */
        $repository = \Yii::$container->get(Token\Repository::class);

        $this->assertTrue(
            Uuid::isValid($repository->put(new Token\Entity('type', 'owner', 'value', $expireAt)))
        );
        Carbon::setTestNow();
    }
}
