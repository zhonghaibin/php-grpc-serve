<?php

namespace App\support;

use App\App;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;

interface JobInterface
{
    public function __construct(App $app);

    public function getTask(): ReceivedTaskInterface;

    public function handler();

    public function setProperty(array $arguments): void;

    public function setTask(ReceivedTaskInterface $task);
}
