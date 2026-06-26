<?php

/**
 * Album Fixtures.
 */

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Album;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class AlbumFixtures.
 */
class AlbumFixtures extends Fixture implements DependentFixtureInterface
{
    public const ALBUM_WITH_COVER = 'album-with-cover';
    public const ALBUM_NO_COVER = 'album-no-cover';
    public const ADMIN_USER = 'admin-user';

    /**
     * Load album fixtures function.
     *
     * @param ObjectManager $manager object manager
     */
    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference(UserFixtures::ADMIN_USER, User::class);
        $cat = $this->getReference(CategoryFixtures::CAT_1, Category::class);

        $album1 = new Album();
        $album1->setTitle('Test Album A');
        $album1->setArtist('Artist A');
        $album1->setReleaseDate(new \DateTime());
        $album1->setAuthor($user);
        $album1->setCategory($cat);

        $manager->persist($album1);
        $this->addReference(self::ALBUM_WITH_COVER, $album1);

        $album2 = new Album();
        $album2->setTitle('Test Album B');
        $album2->setArtist('Artist B');
        $album2->setReleaseDate(new \DateTime());
        $album2->setAuthor($user);
        $album2->setCategory($cat);

        $manager->persist($album2);
        $this->addReference(self::ALBUM_NO_COVER, $album2);

        $manager->flush();
    }

    /**
     * Get dependencies for album.
     *
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CategoryFixtures::class,
        ];
    }
}
