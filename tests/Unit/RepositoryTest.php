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
        $expireDiff = (int)Carbon::getTestNow()->diffInSeconds($expireAt);
        $calls = [];
        \Yii::$container = new Container();
        \Yii::$container->set(
            Token\Repository::class,
            function ($container, $params, $config) use (&$calls) {
                $stub = $this->createMock(Connection::class);

                $stub->expects($this->exactly(6))
                    ->method('__call')
                    ->willReturnCallback(function (string $name, array $params) use (&$calls) {
                        $calls[] = [$name, $params];
                        return null;
                    });

                return new Token\Repository([
                    'redis' => $stub
                ]);
            }
        );

        /** @var Token\Repository $repository */
        $repository = \Yii::$container->get(Token\Repository::class);

        $hash = $repository->put(new Token\Entity('type', 'owner', 'value', $expireAt));
        $this->assertTrue(Uuid::isValid($hash));
        $this->assertEquals(
            [
                ['multi', []],
                ['hset', ["y2rt-{$hash}", 'type', 'type']],
                ['hset', ["y2rt-{$hash}", 'owner', 'owner']],
                ['hset', ["y2rt-{$hash}", 'value', 'value']],
                ['expire', ["y2rt-{$hash}", $expireDiff]],
                ['exec', []],
            ],
            $calls
        );
        Carbon::setTestNow();
    }
}
