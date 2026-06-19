<?php

/**
 * Album voter.
 */

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use App\Entity\Album;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AlbumVoter.
 */
class AlbumVoter extends Voter
{
    public const CREATE = 'CREATE';
    public const EDIT = 'EDIT';
    public const VIEW = 'VIEW';
    private const DELETE = 'DELETE';

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
        return in_array($attribute, [self::CREATE, self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Album;
    }

    /**
     * Performs the access check for the given attribute, subject, and token.
     *
     * @param string         $attribute The attribute to check
     * @param mixed          $subject   The subject to check against
     * @param TokenInterface $token     The token representing the current user
     * @param ?Vote          $vote      The vote
     *
     * @return bool True if the access is granted, false otherwise
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$subject instanceof Album) {
            return false;
        }

        if (null === $user) {
            return self::VIEW === $attribute;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::VIEW => $this->canView(),
            self::CREATE => $this->canCreate($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    /**
     * Determines if the user can edit the given Album.
     *
     * @param Album         $album The Album object
     * @param UserInterface $user  The user attempting to perform the edit operation
     *
     * @return bool True if the user can edit the Album, false otherwise
     */
    private function canEdit(Album $album, UserInterface $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return false;
    }

    /**
     * Determines if the user can create a new Album.
     *
     * @param Album         $album The Album object (unused)
     * @param UserInterface $user  The user attempting to perform the create operation
     *
     * @return bool True if the user can create a new Album, false otherwise
     */
    private function canCreate(Album $album, UserInterface $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return false;
    }

    /**
     * Determines if the user can view any Album.
     *
     * @return bool Always returns true, allowing any user to view Albums
     */
    private function canView(): bool
    {
        return true;
    }

    /**
     * Determines if the user can delete the given Album.
     *
     * @param Album         $album The Album object
     * @param UserInterface $user  The user attempting to perform the delete operation
     *
     * @return bool True if the user can delete the Album, false otherwise
     */
    private function canDelete(Album $album, UserInterface $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return false;
    }
}
