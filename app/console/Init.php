<?php

namespace App\console;

use App\support\ConfigManager;
use App\support\RemoteConfig;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Services\Exception\ServiceException;
use Spiral\RoadRunner\Services\Manager;
use stdClass;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Init
{
    protected ConfigManager $config;

    public function __construct(array $config)
    {
        $this->config = new ConfigManager($config);
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function initServer(): void
    {
        $this->registerService();
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function registerService(): void
    {
        if ($this->config->get('service_register', true)) {
            $client = HttpClient::create(['base_uri' => $this->config->get('service_register_host', 'http://consul:8500') . '/v1/agent/', 'timeout' => 0]);
            $ip     = strtolower($this->config->get('service_ip', 'auto')) == 'auto'
                ? gethostbyname(gethostname())
                : $this->config->get('service_ip');
            $ret    = $client->request('PUT', 'service/register', [
                'json'    => [
                    'ID'                => $this->config->get('service_name'),
                    'NAME'              => $this->config->get('service_name'),
                    'Address'           => $ip,
                    'Port'              => 8000,
                    'Meta'              => new stdClass(),
                    'EnableTagOverride' => false,
                    'Check'             => [
                        'DeregisterCriticalServiceAfter' => '3m',
                        'Grpc'                           => $ip . ':8000',
                        'Interval'                       => '10s',
                    ],
                    'Weights'           => [
                        'Passing' => 1,
                        'Warning' => 1,
                    ],
                ],
                'headers' => [
                    'X-Consul-Token' => $this->config->get('token'),
                ],
            ]);
            file_put_contents('php://stderr', (string)$ret->getContent());
        }
    }

    /**
     * @return void
     * @throws ServiceException
     */
    public function registerWatch(): void
    {
        if ($this->config->get('remote_config')) {
            $manager = new Manager(RPC::fromGlobals());
            $list    = $manager->list();
            if (!$list || !in_array('watch_env', $list)) {
                $manager->create(
                    name: 'watch_env',
                    command: 'php serve.php init:watchEnv',
                    remainAfterExit: true,
                    restartSec: 10,
                );
            }
        }
    }

    /**
     * @return void
     */
    public function watchEnv(): void
    {
        $conf    = new RemoteConfig($this->config->getAll());
        $manager = new Manager(RPC::fromGlobals());
        do {
            $config = $conf->getConfByIndex($this->config->get('remote_config_key'));
            if ($config != $conf->getAll()) {
                $conf->setAll($config);
                $manager->restart('jobs');
                $manager->restart('grpc');
            }
        } while (true);
    }
}
