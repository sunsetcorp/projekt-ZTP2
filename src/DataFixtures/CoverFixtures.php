<?php

/**
 * Cover Fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Cover;
use App\Entity\Album;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class CoverFixtures.
 */
class CoverFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Load cover fixtures.
     *
     * @param ObjectManager $manager object manager
     */
    public function load(ObjectManager $manager): void
    {
        $album = $this->getReference(AlbumFixtures::ALBUM_WITH_COVER, Album::class);

        $cover = new Cover();
        $cover->setFileName('test-cover.jpg');
        $cover->setAlbum($album);

        $manager->persist($cover);
        $manager->flush();
    }

    /**
     * Get dependencies for cover.
     *
     * @return \class-string[]
     */
    public function getDependencies(): array
    {
        return [
            AlbumFixtures::class,
        ];
    }
}
