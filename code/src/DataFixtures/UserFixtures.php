<?php

namespace App\DataFixtures;

use App\Entity\TestEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\SonataUserUser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UserFixtures extends Fixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('fos_user.user_manager');

        /** @var SonataUserUser $user */
        $user = $userManager->createUser();
        $user->setUsername('superuser');
        $user->setPlainPassword('password');
        $user->setEnabled(true);
        $user->setEmail('superuser@example.com');
        $user->setRoles(array('ROLE_SUPER_ADMIN'));

        $userManager->updateUser($user, true);
    }
}