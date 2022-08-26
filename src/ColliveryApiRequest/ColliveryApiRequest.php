<?php

namespace Mds\Collivery\ColliveryApiRequest;

use Mds\Collivery\Cache;

class ColliveryApiRequest extends ApiRequest
{
    protected Cache $cache;
    private array $config;

    public function __construct(array $config, $cache)
    {
        $this->cache = $cache;
        $this->config = $config;
    }

    public function getHost(): string
    {
        return 'api.collivery.co.za';
    }

    public function getProtocol(): string
    {
        return 'https';
    }

    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-App-Name' => 'My Custom App',
            'X-App-Version' => '0.2.1',
            'X-App-Host' => '.NET Framework 4.8',
            'X-App-Lang' => 'C#',
            'X-App-Url' => 'https://example.com',
        ];
    }
}
