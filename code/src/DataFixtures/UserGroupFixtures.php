<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\SonataUserGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserGroupFixtures extends Fixture
{
    public function load(ObjectManager $objectManager)
    {
        $userGroup = new SonataUserGroup('test-group-one',[]);
        $objectManager->persist($userGroup);
        $objectManager->flush();
    }
}