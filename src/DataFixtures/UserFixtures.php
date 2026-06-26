<?php

/**
 *  User fixtures.
 */

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserFixtures.
 *
 * Fixture class for loading initial User data into the database.
 */
class UserFixtures extends Fixture
{
    public const ADMIN_USER = 'admin-user';
    public const NORMAL_USER = 'normal-user';

    /**
     * UserFixtures constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher Password hasher service
     */
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    /**
     * Load method to load user fixtures into the database.
     *
     * @param ObjectManager $manager Doctrine ObjectManager instance
     */
    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);

        $this->loadAdmins($manager);

        $manager->flush();
    }

    /**
     * Load sample users into the database.
     *
     * @param ObjectManager $manager Doctrine ObjectManager instance
     */
    private function loadUsers(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $email = sprintf('user%d@example.com', $i);
            $username = sprintf('user%d', $i);

            $user = $this->createUser(
                $email,
                $username,
                'user1234',
                [UserRole::ROLE_USER]
            );

            $manager->persist($user);

            if (0 === $i) {
                $this->addReference(self::NORMAL_USER, $user);
            }
        }
    }

    /**
     * Load sample admins into the database.
     *
     * @param ObjectManager $manager Doctrine ObjectManager instance
     */
    private function loadAdmins(ObjectManager $manager): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $email = sprintf('admin%d@example.com', $i);
            $username = sprintf('admin%d', $i);

            $user = $this->createUser(
                $email,
                $username,
                'admin1234',
                [UserRole::ROLE_USER, UserRole::ROLE_ADMIN]
            );

            $manager->persist($user);

            if (0 === $i) {
                $this->addReference(self::ADMIN_USER, $user);
            }
        }
    }

    /**
     * Create a User entity.
     *
     * @param string $email    User's email address
     * @param string $username User's username
     * @param string $password User's plain password
     * @param array  $roles    User's roles
     *
     * @return User Created User entity
     */
    private function createUser(string $email, string $username, string $password, array $roles): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        return $user;
    }
}
