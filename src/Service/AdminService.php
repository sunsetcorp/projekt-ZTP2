<?php

/**
 * Admin service.
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\AdminRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Service for administrative operations on users.
 */
class AdminService implements AdminServiceInterface
{
    /**
     * AdminService constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher  The password hasher for hashing user passwords
     * @param PaginatorInterface          $paginator       The paginator service
     * @param AdminRepository             $adminRepository The admin repository
     */
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher, private readonly PaginatorInterface $paginator, private readonly AdminRepository $adminRepository)
    {
    }

    /**
     * Retrieves all users from the database.
     *
     * @return User[] The array of User objects representing all users
     */
    public function getAllUsers(): array
    {
        return $this->adminRepository->findAllUsers();
    }

    /**
     * Retrieves paginated users from the database.
     *
     * @param int $page  The current page number
     * @param int $limit The number of users per page
     *
     * @return PaginationInterface The paginator object containing the users
     */
    public function getPaginatedUsers(int $page = 1, int $limit = 10): PaginationInterface
    {
        return $this->adminRepository->getPaginatedUsers($page, $limit);
    }

    /**
     * Updates the user entity in the database.
     *
     * @param User $user The user entity to update
     */
    public function updateUser(User $user): void
    {
        $this->adminRepository->save($user);
    }

    /**
     * Counts the amount of admins other than current user.
     *
     * @param User $user The user
     *
     * @return int Number of other admins
     */
    public function countOtherAdmins(User $user): int
    {
        return $this->adminRepository->countOtherAdmins($user);
    }

    /**
     * Updates the password of a user entity and persists it.
     *
     * @param User   $user          The user entity for which to update the password
     * @param string $plainPassword The plain password to hash and set for the user
     */
    public function updateUserPassword(User $user, string $plainPassword): void
    {
        $this->adminRepository->updatePassword($user, $plainPassword);
    }
}
