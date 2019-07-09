<?php

namespace AppBundle\DataFixtures\ORM;

use AuthBundle\Entity\Client;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadClientData.
 */
class LoadClientData extends AbstractFixture implements FixtureGroupInterface
{
    public const CLIENT_ID = '0bef2c1f-2d28-4efd-9211-00241ed958cb';
    public const CLIENT_SECRET = 'jLpOJ7S59U30MR9lPmAZmTktV4HGOcbXVYu7kVfYDKqpvN3S';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $client = new Client();
        $client->setRandomId(self::CLIENT_ID);
        $client->setSecret(self::CLIENT_SECRET);
        $client->setAllowedGrantTypes(['client_credentials', 'token', 'refresh_token', 'password']);
        $client->setRedirectUris([]);
        $client->setName('default');
        $this->addReference('auth-client', $client);
        $manager->persist($client);

        $manager->flush();
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['demo', 'default'];
    }

}
