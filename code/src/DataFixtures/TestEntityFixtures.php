<?php

namespace App\DataFixtures;

use App\Entity\TestEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestEntityFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        $testEntityOne = new TestEntity;
        $testEntityOne->setName('Test entity one');
        $manager->persist($testEntityOne);

        $testEntityTwo = new TestEntity;
        $testEntityTwo->setName('Test entity two');
        $manager->persist($testEntityTwo);

        $manager->flush();
    }
}
