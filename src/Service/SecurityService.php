<?php

/**
 * Security service.
 */

namespace App\Service;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityService.
 *
 * Implements methods related to security functionalities.
 */
class SecurityService implements SecurityServiceInterface
{
    /**
     * SecurityService constructor.
     *
     * @param AuthenticationUtils $authenticationUtils The authentication utils service
     */
    public function __construct(private readonly AuthenticationUtils $authenticationUtils)
    {
    }

    /**
     * Retrieves the last authentication error message key.
     *
     * @return string|null The error message key, or null if no error occurred
     */
    public function getLastAuthenticationError(): ?string
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();

        return $error ? $error->getMessageKey() : null;
    }

    /**
     * Retrieves the last username entered by the user during authentication.
     *
     * @return string|null The last username, or null if not available
     */
    public function getLastUsername(): ?string
    {
        return $this->authenticationUtils->getLastUsername();
    }
}
