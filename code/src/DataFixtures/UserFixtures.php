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

        /** @var SonataUserUser $user1 */
        $user1 = $userManager->createUser();
        $user1->setUsername('superuser');
        $user1->setPlainPassword('password');
        $user1->setEnabled(true);
        $user1->setEmail('superuser@example.com');
        $user1->setRoles(array('ROLE_SUPER_ADMIN'));

        $userManager->updateUser($user1, true);

        /** @var SonataUserUser $user2 */
        $user2 = $userManager->createUser();
        $user2->setUsername('normaluser');
        $user2->setPlainPassword('password');
        $user2->setEnabled(true);
        $user2->setEmail('normaluser@example.com');
        $user2->setRoles(array('ROLE_USER'));

        $userManager->updateUser($user2, true);
    }
}