<?php

use Services\Base\HelloWorld\HelloWorldServiceInterface;
use App\services\HelloService;

return [
    'base_path'    => dirname(__DIR__),
    'token'        => env('token', ''),
    'service_name' => env('service_name', 'Service'),
    'service_ip'   => env('service_ip', 'auto'),

    'service_register'      => env('service_register', false),
    'service_register_host' => env('service_register_host', 'http://consul:8500'),

    'remote_config'      => env('remote_config', false),
    'remote_config_host' => env('remote_config_host', 'http://consul:8500'),
    'remote_config_key'  => env('remote_config_key', 'default'),

    // 配置GRPC的实现类和接口
    'service_interface'  => HelloWorldServiceInterface::class,
    'service_class'      => HelloService::class,
];
