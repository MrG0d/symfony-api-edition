<?php

namespace AuthBundle\OAuth\ExternalAuthProvider;

/**
 * Interface ExternalAuthProviderInterface
 */
interface AuthProviderInterface
{
    /**
     * @param string $token
     *
     * @return array
     */
    public function getUserInfo($token);

    /**
     * @return bool
     */
    public function isOnlyAuth();
}
