<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\SonataUserUser;
use App\Service\UserServiceInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ApiUserController
{
    private UserManagerInterface $userManager;
    private ValidatorInterface $validator;
    private UserServiceInterface $userService;
    private array $encoders;
    private array $normalizers;

    public function __construct(
        UserManagerInterface $userManager,
        ValidatorInterface $validator,
        UserServiceInterface $userService
    ) {
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->userService = $userService;
        $this->encoders = [new XmlEncoder, new JsonEncoder];
        $this->normalizers = [new ObjectNormalizer];
    }

    /**
     * @route("/api/user", methods={"POST"}, name="api_user_create")
     * @param Request $request
     * @param TokenStorageInterface $tokenStorage
     * @return JsonResponse
     */
    public function createUser(
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
                'username, email, plainPassword and enabled (bool)';

            return new JsonResponse(['message' => $message], 400);
        }
        try {
            $serializer = new Serializer($this->normalizers, $this->encoders);
            $user = $this->createUserFromRequest($request, $serializer);
            $errors = $this->validator->validate(
                $user,
                null,
                'user_creation'
            );
            if (count($errors) > 0) {
                $message = 'Validation error: ' . $errors[0]->getMessage();
                return new JsonResponse(['message' => $message], 400);
            }
            $this->userManager->updateUser($user);
        } catch (UniqueConstraintViolationException $e) {
            $message = 'Bad request. Username or email is already in use.';
            return new JsonResponse(['message' => $message], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 500);
        }

        return new JsonResponse($serializer->serialize(
            $user,
            'json',
            [
                'json_encode_options' => JSON_UNESCAPED_SLASHES,
                AbstractNormalizer::IGNORED_ATTRIBUTES =>
                    SonataUserUser::attributesToIgnore
            ]
            ),
        201,
        [],
        true
        );
    }

    /**
     * @route("/api/user/{userName}", methods={"DELETE"}, name="api_user_delete")
     * @param string $userName
     * @param Request $request
     * @param TokenStorageInterface $tokenStorage
     * @return JsonResponse
     */
    public function deleteUser(
        string $userName,
        TokenStorageInterface $tokenStorage
    ) : JsonResponse {
        if (
            !$this->userService->checkApiUserIsAdmin($tokenStorage->getToken())
        ) {
            return $this->getUnauthorizedResponse();
        }
        $userToDelete = $this->userManager->findUserByUsername($userName);
        if (is_null($userToDelete)) {
            $message = 'User ' . $userName . ' could not be found.';
            return new JsonResponse(['message' => $message], 404);
        }
        try {
            $this->userManager->deleteUser($userToDelete);
            $message = 'User deleted.';
            return new JsonResponse(['message' => $message], 200);
        } catch (\Exception $e) {
            $message = 'Deletion of user ' . $userName . ' failed.';
            return new JsonResponse(['message' => $message], 500);
        }
    }

    /**
     * @param Request $request
     * @param Serializer $serializer
     * @return mixed
     */
    private function createUserFromRequest(
        Request $request,
        Serializer $serializer
    ) {
        $user = $serializer->deserialize(
            $request->getContent(),
            SonataUserUser::class,
            'json'
        );

        return $user;
    }

    /**
     * @return JsonResponse
     */
    private function getUnauthorizedResponse(): JsonResponse
    {
        $message  = 'You are forbidden from completing this user action. ';
        $message .= '(You must have ROLE_ADMIN or ROLE_SUPER_ADMIN.)';

        return new JsonResponse(['message' => $message], 403);
    }
}