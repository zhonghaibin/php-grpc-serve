<?php
namespace  App\services\Task;
use App\support\BaseJobs;


class  DemoTask extends  BaseJobs{

    public ?string $name;

    public function __construct($app)
    {
        parent::__construct($app);
    }

    public function handler(): void{
        sleep(10);
        var_dump('name:'.$this->name);
    }
}