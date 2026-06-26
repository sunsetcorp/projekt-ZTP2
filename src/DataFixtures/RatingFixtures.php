<?php

/**
 * Rating Fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Rating;
use App\Entity\User;
use App\Entity\Album;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class RatingFixtures.
 */
class RatingFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Load rating fixtures.
     *
     * @param ObjectManager $manager object manager
     */
    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference(UserFixtures::NORMAL_USER, User::class);
        $album = $this->getReference(AlbumFixtures::ALBUM_WITH_COVER, Album::class);

        $rating = new Rating();
        $rating->setUser($user);
        $rating->setAlbum($album);
        $rating->setValue(5);

        $manager->persist($rating);
        $manager->flush();
    }

    /**
     * Get dependencies for rating.
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
