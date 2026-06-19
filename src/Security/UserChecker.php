<?php

/**
 * User checker.
 */

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class User Checker.
 *
 * Checks whether the user is blocked.
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * UserChecker constructor.
     *
     * @param TranslatorInterface $translator The translator interface
     */
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Check user for block pre-authentication.
     *
     * @param UserInterface $user The user interface
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isBlocked()) {
            throw new CustomUserMessageAccountStatusException($this->translator->trans('message.youreblocked'));
        }
    }

    /**
     * Check user for block post-authentication.
     *
     * @param UserInterface $user The user interface
     */
    public function checkPostAuth(UserInterface $user): void
    {
    }
}
