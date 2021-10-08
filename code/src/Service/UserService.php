<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\SonataUserUser;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\GroupInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserService implements UserServiceInterface
{
    /**
     * No constructor property promotion for now without freaking out my iDE.
     * (I'll probably buy a new version before the end of the year.)
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * Type hinting for the TokenStorageInterface input is off for now.
     * It will stay off until I find a way to mock that interface in tests.
     * (Currently, it causes a MethodCannotBeConfiguredException.)
     *
     * @param TokenStorageInterface $user
     * @return bool
     */
    public function checkApiUserIsAdmin(TokenInterface $token) : bool
    {
        $user = $token->getUser();
        if (
            !$user->hasRole('ROLE_ADMIN')  &&
            !$user->hasRole('ROLE_SUPER_ADMIN')
        ) {
            return false;
        }
        return true;
    }

    /**
     * This should eventually be moved to a custom manager class as time allows.
     * (Noted in readme file.)
     *
     * @param GroupInterface $group
     * @return mixed
     */
    public function findByGroup(GroupInterface $group)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('u')
            ->from(SonataUserUser::class, 'u')
            ->join('u.groups', 'g')
            ->where($qb->expr()->eq('g.id', $group->getId()));

        return $qb->getQuery()->getResult();
    }
}