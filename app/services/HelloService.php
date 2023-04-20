<?php

namespace App\services;

use App\services\Task\DemoTask;
use App\support\Queue;
use Services\Base\HelloWorld\HelloWorldServiceInterface;
use Services\Base\HelloWorld\HelloWorldRequest;
use Services\Base\HelloWorld\HelloWorldResponse;

use Spiral\RoadRunner\GRPC;

class HelloService implements HelloWorldServiceInterface
{


    /**
     * 简单实例
     * @param GRPC\ContextInterface $ctx
     * @param HelloWorldRequest $in
     * @return HelloWorldResponse
     */
    public function SayHello(GRPC\ContextInterface $ctx, HelloWorldRequest $in): HelloWorldResponse
    {
        //获取参数
        $name= $in->getName();

        //加入队列
        Queue::push(DemoTask::class,[
            'name'=>$name
        ]);

        $res=new HelloWorldResponse();
        $res->setMessage($name);
        return $res;

    }

}
