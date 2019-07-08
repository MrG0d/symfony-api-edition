<?php

namespace AuthBundle\OAuth\Storage;

use FOS\OAuthServerBundle\Storage\OAuthStorage as BaseStorage;
use OAuth2\Model\IOAuth2Client;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Response;

use AuthBundle\Entity\Client;
use AppBundle\Entity\User;

class OAuthStorage extends BaseStorage
{
    /**
     * @param string $tokenString
     * @param Client $client
     * @param mixed $data
     * @param int $expires
     * @param null $scope
     * @return \FOS\OAuthServerBundle\Model\TokenInterface
     * @throws OAuth2ServerException
     */
    public function createAccessToken($tokenString, IOAuth2Client $client, $data, $expires, $scope = null)
    {
        if ($client instanceof Client && is_object($data) && $data instanceof User) {
            if (!$client->isAllowedUserRole($data)) {
                throw new OAuth2ServerException(
                    Response::HTTP_BAD_REQUEST,
                    'user_not_allowed_by_client',
                    "User is not allowed to use this client"
                );
            }
        }

        return parent::createAccessToken($tokenString, $client, $data, $expires, $scope);
    }
}
