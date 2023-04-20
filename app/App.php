<?php

namespace App;

use Exception;
use App\support\ConfigManager;
use App\support\JobInterface;
use App\support\RemoteConfig;
use InvalidArgumentException;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Environment\Mode;
use Spiral\RoadRunner\GRPC\Server;
use Spiral\RoadRunner\GRPC\ServiceInterface;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Exception\JobsException;
use Spiral\RoadRunner\Jobs\Exception\SerializationException;
use Spiral\RoadRunner\Worker;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

class App
{
    public static array $config;

    /**
     * @param string $file
     * @param bool $forceLocal
     *
     * @return ConfigManager
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function config(string $file = 'app', bool $forceLocal = false): ConfigManager
    {
        $config = static::$config;
        $configArr = !$forceLocal && $config['remote_config']
            ? (new RemoteConfig($config))->getAll()
            : require $config['base_path'] . '/config/' . $file . '.php';
        return new ConfigManager($configArr);
    }

    /**
     * @param array $config
     * @param string $serviceInterface
     * @param string $serviceClass
     *
     * @return void
     * @throws JobsException
     * @throws SerializationException
     * @throws Exception
     */
    public static function run(
        array  $config,
        string $serviceInterface = '',
        string $serviceClass = '',
    ): void
    {
        static::$config = $config;
        $env = Environment::fromGlobals();
        switch ($env->getMode()) {
            case Mode::MODE_GRPC:
                (new self())->grpc($serviceInterface, new $serviceClass());
                break;
            case Mode::MODE_JOBS:
                (new self())->jobs();
                break;
            default:
                (new self())->console();
                break;
        }
    }

    /**
     * @param string $interface
     * @param ServiceInterface $service
     *
     * @return void
     */
    private function grpc(string $interface, ServiceInterface $service): void
    {
        $worker = Worker::create();
        $server = new Server(null, [
            'debug' => false, // optional (default: false)
        ]);
        $server->registerService($interface, $service);
        $server->serve($worker);
    }

    /**
     * @return void
     * @throws JobsException
     * @throws SerializationException
     */
    private function jobs(): void
    {
        $worker = Worker::create();
        $consumer = new Consumer($worker);
        $jobs = [];
        while ($task = $consumer->waitTask()) {
            try {
                $class_name = $task->getName();
                if (!isset($jobs[$class_name])) {
                    if (!class_exists($class_name)) {
                        throw new InvalidArgumentException("Unprocessable task [$class_name]");
                    }
                    $class = new $class_name(app: $this);
                    if (!$class instanceof JobInterface) {
                        throw new InvalidArgumentException("[$class_name] not Jobs");
                    }
                    $jobs[$class_name] = $class;
                }
                $jobs[$class_name]->setTask($task);
                $jobs[$class_name]->setProperty($task->getPayload());
                $jobs[$class_name]->handler();
                $task->complete();
            } catch (JobsException $e) {
                $task->fail($e, true);
            } catch (Throwable $e) {
                $task->fail($e);
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function console(): void
    {
        if (isset($_SERVER['argv']) && $_SERVER['argv'][1]) {
            $cmd = explode(':', $_SERVER['argv'][1], 2);
            if (count($cmd) == 2) {
                $commander = 'App\\console\\' . ucfirst($cmd[0]);
                if (class_exists($commander)) {
                    $command = new $commander(config: static::$config);
                    if (method_exists($command, $cmd[1])) {
                        $command->{$cmd[1]}();
                        return;
                    }
                }
            }
        }
        throw new Exception('Unknown command.');
    }
}
