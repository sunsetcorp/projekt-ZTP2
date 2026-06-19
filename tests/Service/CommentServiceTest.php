<?php

/**
 * Comment service tests.
 */

namespace App\Tests\Service;

use App\Entity\User;
use App\Entity\Album;
use App\Entity\Comment;
use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CommentServiceTest.
 */
class CommentServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private $entityManager;
    /**
     * Comment service.
     */
    private CommentService $commentService;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        $container = static::getContainer();

        $this->entityManager = $container->get('doctrine')->getManager();
        $this->commentService = $container->get(CommentService::class);
    }

    /**
     * Test saving comment.
     */
    public function testSavePersistsComment(): void
    {
        $container = self::getContainer();
        $em = $container->get('doctrine')->getManager();
        $service = $container->get(CommentService::class);

        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('author@test.com');
        $user->setPassword('test');

        $em->persist($user);

        $album = new Album();
        $album->setTitle('Test Album');
        $album->setArtist('Test Album artist');
        $album->setReleaseDate(new \DateTime());
        $album->setAuthor($user);


        $em->persist($album);
        $em->flush();

        $comment = new Comment();
        $comment->setContent('Test comment');
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setAuthor($user);
        $comment->setAlbum($album);

        $service->save($comment);

        $found = $em->getRepository(Comment::class)
            ->findOneBy(['content' => 'Test comment']);

        $this->assertNotNull($found);
    }

    /**
     * Test removing comment.
     */
    public function testRemoveDeletesComment(): void
    {
        $container = self::getContainer();
        $em = $container->get('doctrine')->getManager();
        $service = $container->get(CommentService::class);

        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('author@test.com');
        $user->setPassword('test');

        $em->persist($user);

        $album = new Album();
        $album->setTitle('Test Album');
        $album->setArtist('Test Album artist');
        $album->setReleaseDate(new \DateTime());
        $album->setAuthor($user);


        $em->persist($album);
        $em->flush();

        $comment = new Comment();
        $comment->setContent('Test comment to delete');
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setAuthor($user);
        $comment->setAlbum($album);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $id = $comment->getId();

        $this->commentService->remove($comment);

        $deleted = $this->entityManager
            ->getRepository(Comment::class)
            ->find($id);

        $this->assertNull($deleted);
    }
}
