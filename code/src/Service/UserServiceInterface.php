<?php declare(strict_types=1);

namespace App\Service;

use FOS\UserBundle\Model\GroupInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface UserServiceInterface
{
    /**
     * @param TokenInterface $token
     * @return bool
     */
    public function checkApiUserIsAdmin(TokenInterface $token) : bool;

    /**
     * @param GroupInterface $group
     * @return mixed
     */
    public function findByGroup(GroupInterface $group);
}