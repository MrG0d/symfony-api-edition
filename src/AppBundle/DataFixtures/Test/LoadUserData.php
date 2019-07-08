<?php

namespace AppBundle\DataFixtures\Test;

use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadUserData
 */
class LoadUserData extends AbstractFixture implements FixtureGroupInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('user1@gmail.com');
        $user->setName('User');
        $user->setEnabled(true);
        $user->setPassword('123');
        $this->addReference('user1', $user);

        $manager->persist($user);
        $manager->flush();

        $user = new User();
        $user->setEmail('user2@gmail.com');
        $user->setName('User2');
        $user->setEnabled(true);
        $user->setPassword('123');
        $this->addReference('user2', $user);

        $manager->persist($user);
        $manager->flush();
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return ['test'];
    }
}
