<?php

namespace App\support;

use Exception;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Jobs\OptionsInterface;
use Spiral\RoadRunner\Jobs\QueueInterface;
use Spiral\RoadRunner\Jobs\Task\QueuedTaskInterface;

/**
 * @method QueuedTaskInterface push(string $name, array $payload = [], OptionsInterface $options = null) 创建一个任务
 */
class Queue
{
    /**
     * @param $method
     * @param $args
     *
     * @return void
     * @throws Exception
     */
    public static function __callStatic($method, $args)
    {
        $queue = static::create();
        if (method_exists($queue, $method)) {
            return $queue->$method(...$args);
        } else {
            throw new Exception('Unknown method.');
        }
    }

    /**
     * 取默认队列
     *
     * @param string $queue
     *
     * @return QueueInterface
     */
    public static function create(string $queue = 'local'): QueueInterface
    {
        $jobs = new Jobs();
        return $jobs->connect($queue);
    }
}
