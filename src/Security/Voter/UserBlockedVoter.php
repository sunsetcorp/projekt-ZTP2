<?php

/**
 * Blocked user voter.
 */

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\User;

/**
 * UserBlockedVoter class.
 */
class UserBlockedVoter extends Voter
{
    public const NOT_BLOCKED = 'NOT_BLOCKED';

    /**
     * Determines if this Voter supports the given attribute and subject.
     *
     * @param string $attribute The attribute to check
     * @param mixed  $subject   The subject to check against
     *
     * @return bool True if this Voter supports the attribute and subject, false otherwise
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::NOT_BLOCKED === $attribute;
    }

    /**
     * Performs the access check for the given attribute, subject, and token.
     *
     * @param string         $attribute The attribute to check
     * @param mixed          $subject   The subject to check against
     * @param TokenInterface $token     The token representing the current user
     * @param Vote|null      $vote      The vote
     *
     * @return bool True if the access is granted, false otherwise
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return !$user->isBlocked();
    }
}
