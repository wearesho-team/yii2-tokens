<?php

declare(strict_types=1);

namespace Wearesho\Yii2\Token\Tests\Unit;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use yii\redis\Connection;
use yii\di\Container;
use Wearesho\Yii2\Token;
use Ramsey\Uuid\Uuid;

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
                ];

                $stub->expects($this->at(0))
                    ->method('__call')
                    ->with(
                        'multi'
                    )
                    ->willReturn($this->returnValue(null));

                $stub->expects($this->at(1))
                    ->method('__call')
                    ->willReturn(
                        $this->returnValueMap($map)
                    );

                $stub->expects($this->at(5))
                    ->method('__call')
                    ->with(
                        $this->equalTo('exec')
                    )
                    ->willReturn(
                        $this->returnValue(
                            $this->equalTo(["y2rt-{$hash}", $expireDiff])
                        )
                    );

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
