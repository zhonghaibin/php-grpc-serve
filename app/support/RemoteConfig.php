<?php

namespace App\support;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class RemoteConfig extends ConfigManager
{
    public string $index;
    private array $app_conf;

    /**
     * @param array $app_conf
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function __construct(array $app_conf)
    {
        $this->app_conf = $app_conf;
        parent::__construct($this->getConfByIndex($app_conf['remote_config_key'], '1'));
    }

    /**
     * @param string      $config_key
     * @param string|null $index
     *
     * @return array
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getConfByIndex(string $config_key, ?string $index = null): array
    {
        $client      = HttpClient::create(['base_uri' => $this->app_conf['remote_config_host'] . '/v1/kv/', 'timeout' => 0]);
        $conf        = $client->request('GET', 'service/' . $config_key, [
            'query'   => [
                'raw'   => 'true',
                'index' => $index
                    ?: $this->index,
            ],
            'headers' => [
                'X-Consul-Token' => $this->app_conf['token'],
            ],
        ]);
        $this->index = current($conf->getHeaders()['x-consul-index']);
        return $conf->toArray();
    }
}
