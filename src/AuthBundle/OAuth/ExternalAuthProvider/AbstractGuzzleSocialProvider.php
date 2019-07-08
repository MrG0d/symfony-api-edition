<?php

namespace AuthBundle\OAuth\ExternalAuthProvider;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\UriInterface;

use AuthBundle\OAuth\ExternalAuthProvider\Exception\ExternalAuthException;
use AuthBundle\OAuth\ExternalAuthProvider\Exception\InvalidAccessTokenException;

/**
 * Class AbstractGuzzleSocialProvider
 */
abstract class AbstractGuzzleSocialProvider implements AuthProviderInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * AbstractGuzzleSocialProvider constructor.
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->getBaseUrl(),
        ]);
    }

    /**
     * @return string
     */
    abstract protected function getBaseUrl();

    abstract protected function isInvalidTokenResponse($body);

    /**
     * @param string              $method
     * @param string|UriInterface $url
     * @param array               $params
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request($method, $url, $params = [])
    {
        try {
            $data = $this->client->request($method, $url, $params)->getBody()->getContents();
        } catch (ClientException $exception) {
            $body = json_decode($exception->getResponse()->getBody()->getContents(), true);

            if ($this->isInvalidTokenResponse($body)) {
                throw new InvalidAccessTokenException($exception->getMessage(), 0, $exception);
            }

            throw new ExternalAuthException($exception->getMessage(), 0, $exception);
        }

        return json_decode($data, true);
    }

    /**
     * @return bool
     */
    public function isOnlyAuth(): bool
    {
        return false;
    }
}
