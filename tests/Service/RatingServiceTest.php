<?php

/**
 * Rating service tests.
 */

namespace App\Tests\Service;

use App\Entity\Album;
use App\Entity\Rating;
use App\Entity\User;
use App\Service\RatingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RatingServiceTest.
 */
class RatingServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private EntityManagerInterface $entityManager;
    /**
     * Rating service.
     */
    private RatingService $ratingService;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->ratingService = $container->get(RatingService::class);
    }

    /**
     * Test creating a new rating.
     */
    public function testRateCreatesNewRating(): void
    {
        $user = new User();
        $user->setUsername('rating_user');
        $user->setEmail('rating@test.com');
        $user->setPassword('password');

        $album = new Album();
        $album->setTitle('Test Album');
        $album->setArtist('Test Album artist');
        $album->setReleaseDate(new \DateTime());
        $album->setAuthor($user);

        $this->entityManager->persist($user);
        $this->entityManager->persist($album);
        $this->entityManager->flush();

        $this->ratingService->rate($album, $user, 4);

        $rating = $this->entityManager
            ->getRepository(Rating::class)
            ->findOneBy([
                'album' => $album,
                'user' => $user,
            ]);

        $this->assertNotNull($rating);
        $this->assertEquals(4, $rating->getValue());
    }

    /**
     * Test updating existing rating.
     */
    public function testRateUpdatesExistingRating(): void
    {
        $user = new User();
        $user->setUsername('rating_user2');
        $user->setEmail('rating2@test.com');
        $user->setPassword('password');

        $album = new Album();
        $album->setTitle('Test Album 2');
        $album->setArtist('Test Album artist');
        $album->setReleaseDate(new \DateTime());
        $album->setAuthor($user);

        $this->entityManager->persist($user);
        $this->entityManager->persist($album);

        $rating = new Rating();
        $rating->setAlbum($album);
        $rating->setUser($user);
        $rating->setValue(2);

        $this->entityManager->persist($rating);
        $this->entityManager->flush();

        $this->ratingService->rate($album, $user, 5);

        $updatedRating = $this->entityManager
            ->getRepository(Rating::class)
            ->find($rating->getId());

        $this->assertEquals(5, $updatedRating->getValue());
    }
}
