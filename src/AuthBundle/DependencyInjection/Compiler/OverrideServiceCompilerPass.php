<?php

namespace AuthBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use AuthBundle\Entity\ClientManager;
use AuthBundle\OAuth\Storage\OAuthStorage;

/**
 * Class OverrideServiceCompilerPass.
 */
class OverrideServiceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('fos_oauth_server.client_manager.default')
            ->setClass(ClientManager::class)
        ;

        $container->getDefinition('fos_oauth_server.storage.default')
            ->setClass(OAuthStorage::class)
        ;
    }
}
