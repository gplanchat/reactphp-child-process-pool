<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool\Pool;

use Phake;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Pool\Info;
use WyriHaximus\React\ChildProcess\Pool\Options;
use WyriHaximus\React\ChildProcess\Pool\Pool\Fixed;

class FixedTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $process = Phake::mock('React\ChildProcess\Process');
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        $poolPromise = Fixed::create($process, $loop);
        $this->assertInstanceOf('React\Promise\PromiseInterface', $poolPromise);
        $promiseHasResolved = false;
        $poolPromise->then(function ($pool) use (&$promiseHasResolved) {
            $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Pool\Pool\Fixed', $pool);
            $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Pool\PoolInterface', $pool);
            $promiseHasResolved = true;
        });
        $this->assertTrue($promiseHasResolved);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Given class doesn't implement ChildInterface
     */
    public function testCreateFromClass()
    {
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        Fixed::createFromClass('stdClass', $loop);
    }

    public function testRpc()
    {
        $manager = Phake::mock('WyriHaximus\React\ChildProcess\Pool\ManagerInterface');
        Phake::when($manager)->ping()->thenReturn(true);

        $queue   = Phake::mock('WyriHaximus\React\ChildProcess\Pool\QueueInterface');
        $poolInstance = null;

        $process = Phake::mock('React\ChildProcess\Process');
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        $poolPromise = Fixed::create($process, $loop, [
            Options::MANAGER => $manager,
            Options::QUEUE => $queue,
        ]);
        $this->assertInstanceOf('React\Promise\PromiseInterface', $poolPromise);
        $promiseHasResolved = false;
        $poolPromise->then(function ($pool) use (&$promiseHasResolved, &$poolInstance) {
            $promiseHasResolved = true;
            $poolInstance = $pool;
        });
        $this->assertTrue($promiseHasResolved);

        $this->assertInstanceOf('React\Promise\PromiseInterface', $poolInstance->rpc(Factory::rpc('foo', ['bar'])));

        Phake::verify($manager)->ping();
    }

    public function testInfo()
    {
        $manager = Phake::mock('WyriHaximus\React\ChildProcess\Pool\ManagerInterface');
        Phake::when($manager)->info()->thenReturn([
            Info::TOTAL => 1,
            Info::IDLE  => 2,
            Info::BUSY  => 3,
        ]);

        $queue   = Phake::mock('WyriHaximus\React\ChildProcess\Pool\QueueInterface');
        Phake::when($queue)->count()->thenReturn(4);

        $poolInstance = null;

        $process = Phake::mock('React\ChildProcess\Process');
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        $poolPromise = Fixed::create($process, $loop, [
            Options::MANAGER => $manager,
            Options::QUEUE => $queue,
        ]);
        $this->assertInstanceOf('React\Promise\PromiseInterface', $poolPromise);
        $promiseHasResolved = false;
        $poolPromise->then(function ($pool) use (&$promiseHasResolved, &$poolInstance) {
            $promiseHasResolved = true;
            $poolInstance = $pool;
        });
        $this->assertTrue($promiseHasResolved);

        $this->assertSame([
            Info::BUSY  => 3,
            Info::CALLS => 4,
            Info::IDLE  => 2,
            Info::SIZE  => 1,
        ], $poolInstance->info());
    }

    public function testTerminate()
    {
        $manager = Phake::mock('WyriHaximus\React\ChildProcess\Pool\ManagerInterface');
        Phake::when($manager)->info()->thenReturn([
            Info::TOTAL => 1,
            Info::IDLE  => 2,
            Info::BUSY  => 3,
        ]);

        $queue   = Phake::mock('WyriHaximus\React\ChildProcess\Pool\QueueInterface');
        Phake::when($queue)->count()->thenReturn(4);

        $poolInstance = null;

        $process = Phake::mock('React\ChildProcess\Process');
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        Phake::when($loop)->addTimer($this->isType('integer'), $this->isType('callable'))->thenReturnCallback(function ($interval, $function) {
            $function();
        });
        $poolPromise = Fixed::create($process, $loop, [
            Options::MANAGER => $manager,
            Options::QUEUE => $queue,
        ]);
        $this->assertInstanceOf('React\Promise\PromiseInterface', $poolPromise);
        $promiseHasResolved = false;
        $poolPromise->then(function ($pool) use (&$promiseHasResolved, &$poolInstance) {
            $promiseHasResolved = true;
            $poolInstance = $pool;
        });
        $this->assertTrue($promiseHasResolved);

        $poolInstance->terminate();

        Phake::verify($manager)->terminate();
    }
}
