<?php

namespace Mds\Collivery\ColliveryApiRequest;

use stdClass;

interface ApiRequestContract
{
    public function getHost(): string;

    /**
     * @return string {http|https}
     */
    public function getProtocol(): string;

    public function getHeaders(): array;

    /**
     * @return stdClass
     */
    public function request(string $url, array $urlParameters = [], string $method = 'GET');
}
