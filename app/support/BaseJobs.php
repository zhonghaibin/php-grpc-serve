<?php

namespace App\support;

use App\App;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;

abstract class BaseJobs implements JobInterface
{

    protected App $app;
    private ReceivedTaskInterface $task;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function getTask(): ReceivedTaskInterface
    {
        return $this->task;
    }

    public function setTask(ReceivedTaskInterface $task): void
    {
        $this->task = $task;
    }

    public function setProperty(array $arguments): void
    {
        foreach ($arguments as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
    }
}
