<?php

namespace AuthBundle\OAuth\GrantTypeExtension;

use Doctrine\ORM\EntityManager;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Oneup\UploaderBundle\Uploader\File\FilesystemFile;
use Requestum\ApiBundle\Util\ErrorFactory;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use AppBundle\Entity\User;
use AppBundle\Entity\UserExternalService;
use AppBundle\Entity\File as AppBundleFile;

/**
 * Class ExternalAuthExtension
 */
class ExternalAuthExtension extends AbstractExternalAuthExtension
{
    /**
     * @var string
     */
    protected $appRoot;

    /**
     * ExternalAuthExtension constructor.
     * @param EntityManager         $entityManager
     * @param ValidatorInterface    $validator
     * @param ErrorFactory          $errorFactory
     * @param array                 $providers
     * @param string                $appRoot
     */
    public function __construct(EntityManager $entityManager,
                                ValidatorInterface $validator,
                                ErrorFactory $errorFactory,
                                $providers = [],
                                $appRoot)
    {
        parent::__construct($entityManager, $validator, $errorFactory, $providers);

        $this->appRoot = $appRoot;
    }

    /**
     * @param array  $userInfo
     * @param array  $inputData
     * @throws OAuth2ServerException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     * @return User
     */
    protected function userDoesNotExistAction($userInfo, $inputData)
    {
        $this->validateUserData($userInfo);

        $externalService = new UserExternalService();
        $externalService->setExternalId($userInfo['id']);
        $externalService->setExternalService($inputData['service']);

        $user = new User();
        $user->addExternalService($externalService);
        $user->updateWithExternalData($userInfo);
        $user->setSignUpType($userInfo['sign_up_type']);

        /** @var ConstraintViolation[] $constraints */
        $constraints = $this->validator->validate($user, null, ['Social']);

        if (count($constraints)) {
            $errors = [];

            foreach ($constraints as $constraint) {
                $this->accessor->setValue(
                    $errors,
                    '[' . str_replace('.', '][', $constraint->getPropertyPath()) . ']',
                    $constraint->getMessage()
                );
            }

            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, "unprocessable_user", $errors);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param array $userInfo
     * @throws \Exception
     */
    protected function validateUserData($userInfo)
    {
        if (!$userInfo['id']) {
            throw new \Exception('Cannot get user data');
        }
    }
}
