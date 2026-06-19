<?php

/**
 * Registration service interface.
 */

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface RegistrationServiceInterface.
 *
 * Defines the contract for a registration service.
 */
interface RegistrationServiceInterface
{
    /**
     * Registers a new user.
     *
     * @param User    $user    The user entity to register
     * @param Request $request The request object containing form data
     *
     * @return bool Returns true if registration was successful, false otherwise
     */
    public function register(User $user, Request $request): bool;
}
