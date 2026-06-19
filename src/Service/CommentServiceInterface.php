<?php

/**
 * Comment service interface.
 */

namespace App\Service;

use App\Entity\Comment;

/**
 * Interface CommentServiceInterface.
 *
 * Defines the contract for a comment service.
 */
interface CommentServiceInterface
{
    /**
     * Save a comment entity.
     *
     * @param Comment $comment The comment entity to save
     */
    public function save(Comment $comment): void;

    /**
     * Remove a comment entity.
     *
     * @param Comment $comment The comment entity to remove
     */
    public function remove(Comment $comment): void;
}
