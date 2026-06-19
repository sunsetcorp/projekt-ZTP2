<?php

/**
 * Album repository tests.
 */

namespace App\Tests\Repository;

use App\Repository\AlbumRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AlbumRepositoryTests.
 */
class AlbumRepositoryTest extends KernelTestCase
{
    /**
     * Album Repository.
     */
    private AlbumRepository $albumRepository;

    /**
     * Set up tests.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->albumRepository = self::getContainer()
            ->get(AlbumRepository::class);
    }

    /**
     * Test if repository can be loaded.
     */
    public function testRepositoryCanBeLoaded(): void
    {
        $this->assertInstanceOf(
            AlbumRepository::class,
            $this->albumRepository
        );
    }

    /**
     * Test querying all.
     */
    public function testQueryAll(): void
    {
        $qb = $this->albumRepository->queryAll();

        $this->assertInstanceOf(QueryBuilder::class, $qb);
    }
}
