<?php

namespace AuthBundle\OAuth\ExternalAuthProvider;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class GoogleProvider
 */
class GoogleProvider implements AuthProviderInterface
{
    /**
     * @var string
     */
    private $clientId;
    /**
     * @var string
     */
    private $clientSecret;
    /**
     * @var string
     */
    private $appName;

    /**
     * GoogleProvider constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @param string $appName
     */
    public function __construct($clientId, $clientSecret, $appName)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->appName      = $appName;
    }
    /**
     * {@inheritdoc}
     */
    public function getUserInfo($token)
    {
        $client = new \Google_Client();
        $client->setApplicationName($this->appName);
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setRedirectUri('postmessage');
        $client->addScope('profile');
        $client->addScope('https://www.googleapis.com/auth/userinfo.email');
        $client->addScope('https://www.googleapis.com/auth/userinfo.profile');
        $payload = $client->verifyIdToken($token);
        if(!$payload){
            throw new UnprocessableEntityHttpException('Can not get data from google');
        }
        //todo: try/catch block and payload verify wanted
        $userInfo = [];
        $userInfo['id'] = isset($payload['sub']) ? $payload['sub'] : null;
        $userInfo['first_name'] = isset($payload['given_name']) ? $payload['given_name'] : null;
        $userInfo['last_name'] = isset($payload['family_name']) ? $payload['family_name'] : null;
        $userInfo['avatar'] = isset($payload['picture']) ? $payload['picture'] : null;
        $userInfo['email'] =  isset($payload['email']) ? $payload['email'] : null;

        return $userInfo;
    }

    public function isOnlyAuth()
    {
        return false;
    }
}