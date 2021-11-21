<?php declare(strict_types=1);

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\SonataUserUser;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class UserServiceTest extends TestCase
{
    public function testCheckApiUserIsAdmin()
    {
        $user = new SonataUserUser;

        $projectDir = __DIR__ .
            DIRECTORY_SEPARATOR .
            '..' .
            DIRECTORY_SEPARATOR .
            '..' .
            DIRECTORY_SEPARATOR
        ;

        $entityManagerMock = $this
            ->createMock(EntityManagerInterface::class);
        $tokenMock = $this
            ->createMock(TokenInterface::class);
        $tokenMock->method('getUser')->willReturn($user);

        $userService = new UserService($entityManagerMock, $projectDir);

        $booleanOne = $userService->checkApiUserIsAdmin($tokenMock);
        self::assertFalse($booleanOne);

        $user->addRole('ROLE_ADMIN');
        $booleanTwo = $userService->checkApiUserIsAdmin($tokenMock);
        self::assertTrue($booleanTwo);

        $user->removeRole('ROLE_ADMIN');
        $user->addRole('ROLE_SUPER_ADMIN');
        $booleanThree = $userService->checkApiUserIsAdmin($tokenMock);
        self::assertTrue($booleanThree);

    }
}
