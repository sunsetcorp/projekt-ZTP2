<?php

/**
 * Admin service interface.
 */

namespace App\Service;

use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface AdminServiceInterface.
 *
 * Represents a service interface for administrative operations on users.
 */
interface AdminServiceInterface
{
    /**
     * Retrieves all users from the database.
     *
     * @return User[] The array of User objects representing all users
     */
    public function getAllUsers(): array;

    /**
     * Retrieves paginated users from the database.
     *
     * @param int $page  The current page number
     * @param int $limit The number of users per page
     *
     * @return PaginationInterface The paginator object containing the users
     */
    public function getPaginatedUsers(int $page = 1, int $limit = 10): PaginationInterface;

    /**
     * Counts admins other to the one logged in.
     *
     * @param User $user The current user
     *
     * @return int $count
     */
    public function countOtherAdmins(User $user): int;

    /**
     * Updates the user entity in the database.
     *
     * @param User $user The user entity to update
     */
    public function updateUser(User $user): void;
}
