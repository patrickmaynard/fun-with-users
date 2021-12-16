<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Security\UserChecker;
use Sonata\UserBundle\Entity\BaseUser;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user__user")
 */
class SonataUserUser extends BaseUser
{
    const attributesToIgnore = [
        'createdAt',
        'updatedAt',
        'twoStepVerificationCode',
        'biography',
        'dateOfBirth',
        'facebookData',
        'facebookName',
        'facebookUid',
        'firstname',
        'gplusData',
        'gplusName',
        'gplusUid',
        'lastname',
        'twitterData',
        'twitterName',
        'twitterUid',
        'token',
        'fullname',
        'id',
        'salt',
        'password',
        'confirmationToken',
        'passwordRequestedAt'
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("create")
     */
    protected $id;

    /**
     * @Assert\NotBlank(
     *     groups={"user_creation"},
     *     message = "The username must not be blank."
     * )
     */
    protected $username;

    /**
     * @var int
     * @Assert\Email(
     *     groups={"user_creation"},
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     * @Assert\NotBlank(
     *     groups={"user_creation"},
     *     message = "The email address must not be blank."
     * )
     */
    protected $email;

    /**
     * @Assert\NotBlank(
     *     groups={"user_creation"},
     *     message = "The plainPassword must not be blank."
     * )
     */
    protected $plainPassword;
}