<?php

namespace WyriHaximus\React\ChildProcess\Pool;
use React\EventLoop\LoopInterface;

/**
 * @param string $instanceOf
 * @param array $options
 * @param string $key
 * @param string $fallback
 * @return string
 */
function getClassNameFromOptionOrDefault(array $options, $key, $instanceOf, $fallback)
{
    if (isset($options[$key]) && is_a($options[$key], $instanceOf, true)) {
        return $options[$key];
    }

    return $fallback;
}

/**
 * @param array $options
 * @param $processCollection
 * @param string $default
 * @param LoopInterface $loop
 * @return ManagerInterface
 */
function getManager(array $options, $processCollection, $default, LoopInterface $loop)
{
    $manager = getClassNameFromOptionOrDefault(
        $options,
        Options::MANAGER,
        'WyriHaximus\React\ChildProcess\Pool\ManagerInterface',
        $default
    );

    if ($manager instanceof ManagerInterface) {
        return $manager;
    }

    return new $manager($processCollection, $loop, $options);
}

/**
 * @param array $options
 * @param string $default
 * @return QueueInterface
 */
function getQueue(array $options, $default, $loop)
{
    $queue = getClassNameFromOptionOrDefault(
        $options,
        Options::QUEUE,
        'WyriHaximus\React\ChildProcess\Pool\QueueInterface',
        $default
    );

    if ($queue instanceof QueueInterface) {
        return $queue;
    }

    return new $queue($loop);
}
