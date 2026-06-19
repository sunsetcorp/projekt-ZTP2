<?php

/**
 * Security service interface.
 */

namespace App\Service;

/**
 * Interface SecurityServiceInterface.
 *
 * Defines methods related to security services.
 */
interface SecurityServiceInterface
{
    /**
     * Retrieves the last authentication error message key.
     *
     * @return string|null The error message key, or null if no error occurred
     */
    public function getLastAuthenticationError(): ?string;

    /**
     * Retrieves the last username entered by the user during authentication.
     *
     * @return string|null The last username, or null if not available
     */
    public function getLastUsername(): ?string;
}
