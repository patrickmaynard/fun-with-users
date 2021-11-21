<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\SonataUserUser;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\GroupInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Yaml\Parser;

class UserService implements UserServiceInterface
{
    /**
     * No constructor property promotion for now without freaking out my iDE.
     * (I'll probably buy a new version before the end of the year.)
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    private $projectDir;

    private $roleHierarchy;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $projectDir
    ) {
        $this->entityManager = $entityManager;
        $this->projectDir = $projectDir;
        $this->roleHierarchy = $this->initializeRoleHierarchy();
    }

    /**
     * @param TokenInterface $token
     * @return bool
     */
    public function checkApiUserIsAdmin(TokenInterface $token) : bool
    {
        /** @var SonataUserUser $user */
        $user = $token->getUser();
        $allRolesFlat = $this->getAllRolesFlat($user);
        if (in_array('ROLE_ADMIN', $allRolesFlat)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $roleName
     * @return array
     */
    public function getAllSubRolesRecursively(string $roleName) : array
    {
        // We might already be at the bottom of the hierarchy.
        // If so, just return this name in an array.
        if (!array_key_exists($roleName, $this->roleHierarchy)) {
            return [$roleName];
        }

        //But if we're not at the bottom yet, we want an array of all subroles.
        $roles = [];
        $roles[] = $roleName;
        foreach ($this->roleHierarchy[$roleName] as $key => $value) {
            $roles = array_merge(
                $roles, $this->getAllSubRolesRecursively($value)
            );
        }
        return $roles;
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

    /**
     * @return mixed
     */
    private function initializeRoleHierarchy()
    {
        $yaml = new Parser();
        $arrayOfOptions = $yaml->parse(
            file_get_contents(
                $this->projectDir.'/config/packages/security.yaml'
            )
        );
        $roleHierarchy = $arrayOfOptions['security']['role_hierarchy'];

        return $roleHierarchy;
    }

    /**
     * @param SonataUserUser $user
     * @return array
     */
    private function getAllRolesFlat(SonataUserUser $user): array
    {
        $topLevelRolesIncludingGroupRoles = $user->getRoles();
        $allRolesFlat = [];
        foreach ($topLevelRolesIncludingGroupRoles as $roleName) {
            foreach (
                $this->getAllSubRolesRecursively($roleName) as $extraRole
            ) {
                if (!in_array($extraRole, $allRolesFlat)) {
                    $allRolesFlat[] = $extraRole;
                }
            }
        }

        return $allRolesFlat;
    }
}