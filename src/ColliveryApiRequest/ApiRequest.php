<?php

namespace Mds\Collivery\ColliveryApiRequest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class ApiRequest implements ApiRequestContract
{
    /**
     * @throws HttpException
     */
    public function request(string $url, array $urlParameters = [], string $method = 'GET'): array
    {
        return $this->handleRequest($url, $urlParameters, $method);
    }

    public function getData(array $data = []): array
    {
        return $data;
    }

    protected function getUri(string $path, array $extraData = []): Uri
    {
        $path = $this->parsePath($path);
        $query = http_build_query($this->getData($extraData), null, '&', PHP_QUERY_RFC3986);
        $protocol = $this->getProtocol();
        $host = $this->getHost();

        return (new Uri())
            ->withScheme($protocol)
            ->withHost($host)
            ->withPath($path)
            ->withQuery($query);
    }

    protected function getRequest(Uri $uri, string $method = 'GET', array $requestBody = []): Request
    {
        $data = $this->getData($requestBody);
        $headers = $this->getHeaders();

        $headers['Content-Type'] ??= 'application/x-www-form-urlencoded';

        if (is_array($data) || is_object($data)) {
            if ($headers['Content-Type'] === 'application/json') {
                $data = json_encode($data);
            } else {
                $data = http_build_query($data);
            }
        }

        return new Request($method, $uri, $headers, $data);
    }

    /**
     * @throws HttpException
     */
    protected function getResponse(Request $request): ResponseInterface
    {
        try {
            $client = new Client();

            return $client->send($request);
        } catch (GuzzleException $e) {
            $response = method_exists($e, 'getResponse') ? $e->getResponse() : null;
            $message = $e->getMessage();

            // There isn't always a response in case of no server found or timeout
            // Or if an exception like \GuzzleHttp\Exception\ServerException is thrown
            if ($response) {
                $message = $this->parseErrorMessage($response) ?: $message; // Fallback to exception message if response has no content

                throw new HttpException($response->getStatusCode(), $message, $e, $response->getHeaders(), $e->getCode());
            }

            throw new HttpException(500, $message, $e->getPrevious(), [], $e->getCode());
        }
    }

    protected function parsePath(string $path): string
    {
        return str_replace('//', '/', $path);
    }

    protected function parseErrorMessage(Response $response): string
    {
        // uf the token expired and we have an unauthentcated error then clear the token
        $contents = (string) $response->getBody()->getContents();
        // Allow for the body not containing Json
        $contents = json_decode($contents) ?: $contents;

        if (is_object($contents) && property_exists($contents, 'error')) {
            $contents = $contents->error;
        }
        if (is_object($contents) && property_exists($contents, 'errors')) {
            $contents = $contents->errors;
        }

        if (is_object($contents) && property_exists($contents, 'message')) {
            return $contents->message;
        }

        // Because parsing the error messages from Laravel's validation layer sucks...
        $message = '';
        foreach ((array) $contents as $key => $error) {
            if (!is_numeric($key)) {
                $message .= Str::title(str_replace(['-', '_'], [' ', ' '], $key)).': ';
            }

            if (is_array($error) || is_object($error)) {
                $message .= implode(', ', (array) $error);
            } else {
                $message .= $error;
            }
        }

        return $message;
    }

    private function handleRequest(string $url, array $urlParameters, string $method): array
    {
        $method = strtoupper($method);
        $uri = $this->getUri($url, $urlParameters);
        $request = $this->getRequest($uri, $method, $urlParameters);
        $response = $this->getResponse($request);

        $contents = $response->getBody();
        $response = json_decode($contents, true);

        return $response['data'] ?? [];
    }
}
