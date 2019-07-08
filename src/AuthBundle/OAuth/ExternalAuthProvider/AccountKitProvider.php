<?php

namespace AuthBundle\OAuth\ExternalAuthProvider;

use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AccountKitProvider
 */
class AccountKitProvider extends AbstractGuzzleSocialProvider
{

    /**
     * {@inheritdoc}
     */
    public function getUserInfo($token)
    {
        $data = $this->request(Request::METHOD_GET, '/me', [
            'query' => [
                'access_token' => $token,
            ],
        ]);

        $this->deleteToken($token);

        $userInfo = [];
        $userInfo['id'] = isset($data['id']) ? $data['id'] : null;
        $userInfo['email'] = isset($data['email']) ? $data['email']['address'] : null;
        $userInfo['phone'] = isset($data['phone']) ? $data['phone']['country_prefix'] . $data['phone']['national_number'] : null;
        $userInfo['sign_up_type'] = User::SIGN_UP_TYPE_ACCOUNT_KIT;

        return $userInfo;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBaseUrl()
    {
        return 'https://graph.accountkit.com/v1.3';
    }

    /**
     * {@inheritdoc}
     */
    protected function isInvalidTokenResponse($body)
    {
        return isset($body['error']['code']) && $body['error']['code'] == 190;
    }

    /**
     * @param $token
     *
     * @return bool
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteToken($token)
    {
        $data = $this->request(Request::METHOD_POST, '/logout', [
            'query' => [
                'access_token' => $token,
            ],
        ]);

        return isset($data['success']) && $data['success'] === true;
    }

    /**
     * @return bool
     */
    public function isOnlyAuth(): bool
    {
        return true;
    }
}
