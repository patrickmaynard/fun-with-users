<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\SonataUserGroup;
use App\Entity\SonataUserUser;
use App\Service\UserServiceInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use FOS\UserBundle\Model\GroupManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiUserGroupController
{
    //GroupManagerInterface is marked as deprecated.
    //But no alternative is given, so we use it for now.
    private UserServiceInterface $userService;
    private ValidatorInterface $validator;
    private GroupManagerInterface $userGroupManager;
    private UserManagerInterface $userManager;
    private array $encoders;
    private array $normalizers;

    public function __construct(
        UserServiceInterface $userService,
        ValidatorInterface $validator,
        GroupManagerInterface $userGroupManager,
        UserManagerInterface $userManager
    ) {
        $this->userService = $userService;
        $this->validator = $validator;
        $this->userGroupManager = $userGroupManager;
        $this->userManager = $userManager;
        $this->encoders = [new XmlEncoder, new JsonEncoder];
        $this->normalizers = [new ObjectNormalizer];
    }

    /**
     * @route("/api/user-group", methods={"POST"}, name="api_user_group_create")
     * @param Request $request
     * @param TokenStorageInterface $tokenStorage
     * @return JsonResponse
     */
    public function createUserGroup(
        Request $request,
        TokenStorageInterface $tokenStorage
    ) : JsonResponse {
        if (
            !$this->userService->checkApiUserIsAdmin($tokenStorage->getToken())
        ) {
            return $this->getUnauthorizedResponse();
        }
        if (!$submittedData = json_decode($request->getContent())) {
            $message = 'Bad request. Please provide JSON fields describing a '.
                'group name and a list of roles.';

            return new JsonResponse(['message' => $message], 400);
        }
        try {
            $serializer = new Serializer($this->normalizers, $this->encoders);
            $userGroup = $this->createUserGroupFromRequest($request, $serializer);
            $errors = $this->validator->validate($userGroup);
            if (count($errors) > 0) {
                $message = 'Validation error: ' . $errors[0]->getMessage();
                return new JsonResponse(['message' => $message], 400);
            }
            $this->userGroupManager->updateGroup($userGroup);
        } catch (UniqueConstraintViolationException $e) {
            $message = 'Bad request. Group name is already in use.';
            return new JsonResponse(['message' => $message], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 500);
        }

        return new JsonResponse($serializer->serialize(
            $userGroup,
            'json',
            [
                'json_encode_options' => JSON_UNESCAPED_SLASHES
            ]
        ),
            201,
            [],
            true
        );
    }

    /**
     * @route(
     *     "/api/user-group/{userGroupName}",
     *     methods={"DELETE"},
     *     name="api_user_group_delete"
     * )
     * @param string $userGroupName
     * @param TokenStorageInterface $tokenStorage
     * @return JsonResponse
     */
    public function deleteUserGroup(
        string $userGroupName,
        TokenStorageInterface $tokenStorage
    ) : JsonResponse {
        if (
            !$this->userService->checkApiUserIsAdmin($tokenStorage->getToken())
        ) {
            return $this->getUnauthorizedResponse();
        }
        $userGroupToDelete = $this
            ->userGroupManager
            ->findGroupByName($userGroupName)
        ;
        if (is_null($userGroupToDelete)) {
            $message = 'Group ' . $userGroupName . ' could not be found.';
            return new JsonResponse(['message' => $message], 404);
        }
        $usersInGroup = $this->userService->findByGroup($userGroupToDelete);
        if (count($usersInGroup) !== 0) {
            $message = 'The group ' . $userGroupName .
                ' is not empty and so was not deleted.';
            return new JsonResponse(['message' => $message], 400);
        }
        try {
            $this->userGroupManager->deleteGroup($userGroupToDelete);
            $message = 'Group deleted.';
            return new JsonResponse(['message' => $message], 200);
        } catch (\Exception $e) {
            $message = 'Deletion of group ' . $userGroupName . ' failed.';
            return new JsonResponse(['message' => $message], 500);
        }
    }

    /**
     * @route(
     *     "/api/user-group/{userGroupName}/{userName}",
     *     methods={"LINK"},
     *     name="api_user_group_add_user"
     * )
     * @param string $userGroupName
     * @param string $userName
     * @param TokenStorageInterface $tokenStorage
     * @return JsonResponse
     */
    public function addUserToUserGroup(
        string $userGroupName,
        string $userName,
        TokenStorageInterface $tokenStorage
    ) : JsonResponse
    {
        if (
            !$this->userService->checkApiUserIsAdmin($tokenStorage->getToken())
        ) {
            return $this->getUnauthorizedResponse();
        }
        $userGroupToAddTo = $this
            ->userGroupManager
            ->findGroupByName($userGroupName)
        ;
        if (is_null($userGroupToAddTo)) {
            $message = 'Group ' . $userGroupName . ' could not be found.';
            return new JsonResponse(['message' => $message], 404);
        }
        /** @var SonataUserUser $userToAdd */
        $userToAdd = $this
            ->userManager
            ->findUserByUsername($userName)
        ;
        if (is_null($userToAdd)) {
            $message = 'User ' . $userName . ' could not be found.';
            return new JsonResponse(['message' => $message], 404);
        }
        try {
            if (!$userToAdd->hasGroup($userGroupName)) {
                $userToAdd->addGroup($userGroupToAddTo);
                $this->userManager->updateUser($userToAdd);
            } else {
                $message = 'User ' . $userName . ' is already in this group.';
                return new JsonResponse(['message' => $message], 400);
            }
        } catch (\Exception $e) {
            $message = 'Addition of user ' . $userName . ' failed.';
            return new JsonResponse(['message' => $message], 500);
        }
        $message = 'Addition of user ' . $userName . ' succeeded.';
        return new JsonResponse(['message' => $message], 200);
    }

    /**
     * @route(
     *     "/api/user-group/{userGroupName}/{userName}",
     *     methods={"UNLINK"},
     *     name="api_user_group_remove_user"
     * )
     * @param string $userGroupName
     * @param string $userName
     * @param TokenStorageInterface $tokenStorage
     * @return JsonResponse
     */
    public function removeUserFromUserGroup(
        string $userGroupName,
        string $userName,
        TokenStorageInterface $tokenStorage
    ) : JsonResponse
    {
        if (
            !$this->userService->checkApiUserIsAdmin($tokenStorage->getToken())
        ) {
            return $this->getUnauthorizedResponse();
        }
        $userGroupToRemoveFrom = $this
            ->userGroupManager
            ->findGroupByName($userGroupName)
        ;
        if (is_null($userGroupToRemoveFrom)) {
            $message = 'Group ' . $userGroupName . ' could not be found.';
            return new JsonResponse(['message' => $message], 404);
        }
        /** @var SonataUserUser $userToRemove */
        $userToRemove = $this
            ->userManager
            ->findUserByUsername($userName)
        ;
        if (is_null($userToRemove)) {
            $message = 'User ' . $userName . ' could not be found.';
            return new JsonResponse(['message' => $message], 404);
        }
        try {
            if ($userToRemove->hasGroup($userGroupName)) {
                $userToRemove->removeGroup($userGroupToRemoveFrom);
                $this->userManager->updateUser($userToRemove);
            } else {
                $message = 'User ' . $userName . ' was not in this group.';
                return new JsonResponse(['message' => $message], 400);
            }
        } catch (\Exception $e) {
            $message = 'Removal of user ' . $userName . ' failed.';
            return new JsonResponse(['message' => $message], 500);
        }
        $message = 'Removal of user ' . $userName . ' succeeded.';
        return new JsonResponse(['message' => $message], 200);
    }

    /**
     * @param Request $request
     * @param Serializer $serializer
     * @return mixed
     */
    private function createUserGroupFromRequest(
        Request $request,
        Serializer $serializer
    ) {
        $userGroup = $serializer->deserialize(
            $request->getContent(),
            SonataUserGroup::class,
            'json'
        );

        return $userGroup;
    }

    /**
     * @return JsonResponse
     */
    private function getUnauthorizedResponse(): JsonResponse
    {
        $message  = 'You are forbidden from completing this group action. ';
        $message .= '(You must have ROLE_ADMIN or ROLE_SUPER_ADMIN.)';

        return new JsonResponse(['message' => $message], 403);
    }
}