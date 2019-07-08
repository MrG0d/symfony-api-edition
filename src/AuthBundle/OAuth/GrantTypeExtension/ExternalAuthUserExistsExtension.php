<?php

namespace AuthBundle\OAuth\GrantTypeExtension;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ExternalAuthExtension
 */
class ExternalAuthUserExistsExtension extends AbstractExternalAuthExtension
{

    /**
     * @param array  $userInfo
     * @param array $inputData
     * @return mixed|void
     */
    protected function userDoesNotExistAction($userInfo, $inputData)
    {
        throw new NotFoundHttpException('User account not found for external service account from '.$inputData['service']);
    }
}
