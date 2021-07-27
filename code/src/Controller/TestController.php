<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\TestEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class TestController
{

    private EntityManagerInterface $entityManager;
    private array $encoders;
    private array $normalizers;

    /**
     * TestController constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->encoders = [new XmlEncoder, new JsonEncoder];
        $this->normalizers = [new ObjectNormalizer];
    }

    /**
     * @Route("/basic-test", name="app_basic_test")
     */
    public function basicTest(): JsonResponse
    {
        $serializer = new Serializer($this->normalizers, $this->encoders);
        $testEntityRepository = $this->entityManager->getRepository(
            TestEntity::class
        );
        $deserializedEntities = [];
        $testEntities = $testEntityRepository->findAll();
        foreach ($testEntities as $entity) {
            $deserializedEntities[] = json_decode(
                $serializer->serialize($entity, 'json')
            );
        }

        return new JsonResponse($deserializedEntities);
    }
}
