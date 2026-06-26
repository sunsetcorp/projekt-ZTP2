<?php

/**
 * Comment Fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Album;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class CommentFixtures.
 */
class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Load comment fixtures.
     *
     * @param ObjectManager $manager object manager
     */
    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference(UserFixtures::NORMAL_USER, User::class);
        $album = $this->getReference(AlbumFixtures::ALBUM_WITH_COVER, Album::class);

        $comment = new Comment();
        $comment->setAuthor($user);
        $comment->setAlbum($album);
        $comment->setContent('Great album!');
        $comment->setCreatedAt(new \DateTimeImmutable());

        $manager->persist($comment);
        $manager->flush();
    }

    /**
     * Load dependencies for comments.
     *
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            AlbumFixtures::class,
        ];
    }
}
