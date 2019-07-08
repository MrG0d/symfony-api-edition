<?php

namespace AuthBundle\OAuth\GrantTypeExtension;

use Doctrine\ORM\EntityManager;
use FOS\OAuthServerBundle\Storage\GrantExtensionInterface;
use OAuth2\Model\IOAuth2Client;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Requestum\ApiBundle\Util\ErrorFactory;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use AuthBundle\OAuth\ExternalAuthProvider\AuthProviderInterface;
use AuthBundle\OAuth\ExternalAuthProvider\Exception\InvalidAccessTokenException;
use AppBundle\Entity\UserExternalService;
use AppBundle\Entity\User;

/**
 * Class ExternalAuthExtension
 */
abstract class AbstractExternalAuthExtension implements GrantExtensionInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var ErrorFactory
     */
    protected $errorFactory;

    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    /**
     * @var AuthProviderInterface[]
     */
    protected $providers;

    /**
     * SocialGrantExtension constructor.
     * @param EntityManager           $entityManager
     * @param ValidatorInterface      $validator
     * @param ErrorFactory            $errorFactory
     * @param AuthProviderInterface[] $providers
     */
    public function __construct(
        EntityManager $entityManager,
        ValidatorInterface $validator,
        ErrorFactory $errorFactory,
        array $providers = []
    )
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->errorFactory = $errorFactory;
        $this->accessor = PropertyAccess::createPropertyAccessor();

        foreach ($providers as $name => $provider) {
            $this->addProvider($name, $provider);
        }
    }

    /**
     * @param array $userInfo
     * @param array $inputData
     *
     * @return User|void
     */
    abstract protected function userDoesNotExistAction($userInfo, $inputData);

    /**
     * @param string                $name
     * @param AuthProviderInterface $provider
     */
    public function addProvider($name, AuthProviderInterface $provider)
    {
        $this->providers[$name] = $provider;
    }

    /**
     * @param IOAuth2Client $client
     * @param array         $inputData
     * @param array         $authHeaders
     *
     * @return array|bool
     *
     * @throws OAuth2ServerException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function checkGrantExtension(IOAuth2Client $client, array $inputData, array $authHeaders)
    {
        if (!isset($this->providers[$inputData['service']])) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, "unsupported_service");
        }

        try {
            /** @var array $userInfo */
            $userInfo = $this->providers[$inputData['service']]->getUserInfo($inputData['token']);
        } catch (InvalidAccessTokenException $exception) {
            return false;
        }

        $repository = $this->entityManager->getRepository(User::class);

        if (null === ($user = $repository->findUserByExternalServiceInfo($userInfo['id'], $inputData['service']))) {

            if ((isset($userInfo['email']) && $user = $repository->findOneBy(['email' => $userInfo['email']])) ||
                (isset($userInfo['phone']) && $user = $repository->findOneBy(['phone' => $userInfo['phone']]))) {

                /**@var UserExternalService $externalService */
                foreach ($user->getExternalServices() as $externalService) {
                    if ($externalService->getExternalService() == $inputData['service']) {
                        throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST,
                            'Error. User with this email is already have a linked account from ' . $inputData['service']);
                    }
                }

                $externalService = new UserExternalService();
                $externalService->setExternalId($userInfo['id']);
                $externalService->setExternalService($inputData['service']);
                $externalService->setUser($user);

                $this->entityManager->persist($externalService);
                $this->entityManager->flush();

            } else {
                if (!$this->providers[$inputData['service']]->isOnlyAuth()) {
                    $user = $this->userDoesNotExistAction($userInfo, $inputData);
                } else {
                    throw new BadRequestHttpException();
                }
            }
        }

        return ['data' => $user];
    }

}
