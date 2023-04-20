<?php

use App\App;
use Spiral\RoadRunner\Jobs\Exception\JobsException;
use Spiral\RoadRunner\Jobs\Exception\SerializationException;
use Symfony\Component\Dotenv\Dotenv;

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        $value = $_ENV[strtoupper($key)] ?? $default;
        if (strtolower($value) === 'true') {
            return true;
        } elseif (strtolower($value) === 'false') {
            return false;
        } else {
            return $value;
        }
    }
}

require __DIR__ . '/vendor/autoload.php';
//判断是否需要加载ENV
try {
    if(empty($_ENV) && file_exists('.env')){
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/.env');
    }
}catch (Throwable $e){}


$config = require __DIR__ . '/config/app.php';
try {
    App::run(
        config: $config,
        serviceInterface: $config['service_interface'],
        serviceClass: $config['service_class'],
    );
} catch (SerializationException|JobsException|Exception|Throwable $e) {
    file_put_contents('php://stderr', $e->getMessage());
}
