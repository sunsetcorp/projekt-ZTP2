<?php

/**
 *Account service interface.
 */

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface AccountServiceInterface.
 *
 * Defines methods for managing user account operations.
 */
interface AccountServiceInterface
{
    /**
     * Retrieves the currently authenticated user.
     *
     * @return User|null The current user object, or null if not authenticated
     */
    public function getCurrentUser(): ?User;

    /**
     * Renders the account page for the current user.
     *
     * @return Response The response object containing the rendered account page
     */
    public function renderAccountPage(): Response;

    /**
     * Handles the account editing process based on submitted form data.
     *
     * @param Request $request The current request object containing form data
     *
     * @return Response The response object rendering the account edit or error page
     *
     * @throws \RuntimeException If an error occurs during form handling or entity persistence
     */
    public function handleAccountEdit(Request $request): Response|array;
}
