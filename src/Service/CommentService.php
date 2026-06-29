<?php

/**
 * Comment service.
 */

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Album;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class CommentService.
 *
 * Service class for managing comments.
 */
class CommentService implements CommentServiceInterface
{
    /**
     * CommentService constructor.
     *
     * @param CommentRepository      $commentRepository The comment repository
     * @param EntityManagerInterface $entityManager     The entity manager
     */
    public function __construct(private readonly CommentRepository $commentRepository, private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Save a comment entity.
     *
     * @param Comment $comment The comment entity to save
     */
    public function save(Comment $comment): void
    {
        $this->commentRepository->save($comment, true);
    }

    /**
     * Remove a comment entity.
     *
     * @param Comment $comment The comment entity to remove
     */
    public function remove(Comment $comment): void
    {
        $this->commentRepository->remove($comment, true);
    }

    /**
     * Get comments for album page.
     *
     * @param Album $album The album
     *
     * @return array Array of comments
     */
    public function getCommentsByAlbum(Album $album): array
    {
        return $this->commentRepository->findBy(['album' => $album]);
    }
}
