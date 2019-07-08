<?php

namespace AuthBundle\OAuth\ExternalAuthProvider;

use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FacebookProvider
 */
class FacebookProvider extends AbstractGuzzleSocialProvider
{
    const USER_PICTURE_RESOLUTION = 500;

    /**
     * {@inheritdoc}
     */
    public function getUserInfo($token)
    {
        $data = $this->request(Request::METHOD_GET, '/me', [
            'query' => [
                'fields' => 'id,first_name,last_name,email,hometown',
                'access_token' => $token,
            ],
        ]);

        $userInfo = [];
        $userInfo['id'] = isset($data['id']) ? $data['id'] : null;
        $userInfo['first_name'] = isset($data['first_name']) ? $data['first_name'] : null;
        $userInfo['last_name'] = isset($data['last_name']) ? $data['last_name'] : null;
        $userInfo['email'] = isset($data['email']) ? $data['email'] : null;
        $userInfo['hometown'] = isset($data['hometown']) ? $data['hometown'] : null;
        $userInfo['sign_up_type'] = User::SIGN_UP_TYPE_FACEBOOK;

        /*
         * according to https://developers.facebook.com/docs/graph-api/reference/user/picture/
         */
        $pictureData = $this->request(Request::METHOD_GET, '/me/picture', [
            'query' => [
                'access_token' => $token,
                'redirect' => 'false',
                'width' => self::USER_PICTURE_RESOLUTION,
                'height' => self::USER_PICTURE_RESOLUTION,
            ],
        ]);

        if ($pictureData && isset($pictureData['data']['url'])) {
            $userInfo['avatar'] = $pictureData['data']['url'];
        }

        return $userInfo;
    }

    /**
     * @param string $token
     *
     * @return array|null
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getFriendsIds($token)
    {
        $queryParams =
            [
                'fields' => 'id',
                'access_token' => $token,
                'limit' => 100,
            ];
        $usersId = [];
        do {
            $response = $this->request(Request::METHOD_GET, '/me/friends', ['query' => $queryParams]);

            $userInfo = isset($response['data']) ? $response['data'] : null;
            if (!empty($userInfo)) {
                $usersId = array_merge($usersId, array_column($userInfo, 'id'));
            }

            $queryParams ['after'] = isset($response['paging']['next']) ? $response['paging']['cursors']['after'] : null;

        } while (isset($response['paging']['next']));

        return $usersId;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBaseUrl()
    {
        return 'https://graph.facebook.com';
    }

    /**
     * {@inheritdoc}
     */
    protected function isInvalidTokenResponse($body)
    {
        return isset($body['error']['code']) && $body['error']['code'] == 190;
    }
}
