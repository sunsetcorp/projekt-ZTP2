<?php

/**
 * Album service tests.
 */

namespace App\Tests\Service;

use PHPUnit\Framework\MockObject\Exception;
use App\Entity\Album;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\AlbumService;
use App\Repository\AlbumRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AlbumServiceTest.
 */
class AlbumServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private EntityManagerInterface $entityManager;
    /**
     * Album service.
     */
    private AlbumService $albumService;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->albumService = $container->get(AlbumService::class);
    }

    /**
     * Test saving an album.
     */
    public function testSave(): void
    {
        $user = $this->createUser();

        $album = new Album();
        $album->setTitle('Test Album');
        $album->setArtist('Test Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setAuthor($user);

        $this->albumService->save($album);

        $result = $this->entityManager
            ->createQueryBuilder()
            ->select('a')
            ->from(Album::class, 'a')
            ->where('a.title = :title')
            ->setParameter('title', 'Test Album')
            ->getQuery()
            ->getSingleResult();

        $this->assertSame('Test Album', $result->getTitle());
    }

    /**
     * Test deleting an album.
     */
    public function testDelete(): void
    {
        $user = $this->createUser();

        $album = new Album();
        $album->setTitle('To Delete');
        $album->setArtist('Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setAuthor($user);

        $this->entityManager->persist($album);
        $this->entityManager->flush();

        $id = $album->getId();

        $this->albumService->delete($album);

        $result = $this->entityManager
            ->createQueryBuilder()
            ->select('a')
            ->from(Album::class, 'a')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($result);
    }

    /**
     * Test getting albums by tag.
     */
    public function testGetAlbumsByTag(): void
    {
        $tag = new Tag();
        $tag->setTitle('rock');

        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        $result = $this->albumService->getAlbumsByTag($tag);

        $this->assertIsArray($result);
    }

    /**
     * test toggling favourite.
     */
    public function testToggleFavorite(): void
    {
        $user = $this->createUser();

        $album = new Album();
        $album->setTitle('Fav Album');
        $album->setArtist('Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setAuthor($user);

        $this->entityManager->persist($album);
        $this->entityManager->flush();

        $this->albumService->toggleFavorite($album, $user);

        $this->assertTrue($user->getFavorites()->contains($album));
    }

    /**
     * Test removing a favourite.
     */
    public function testRemoveFavorite(): void
    {
        $user = $this->createUser();

        $album = new Album();
        $album->setTitle('Fav Album');
        $album->setArtist('Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setAuthor($user);

        $this->entityManager->persist($album);
        $this->entityManager->flush();

        $user->addFavorite($album);

        $this->albumService->removeFavorite($album->getId(), $user);

        $this->assertFalse($user->getFavorites()->contains($album));
    }

    /**
     * Test getting a paginated list.
     */
    public function testGetPaginatedList(): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $album = new Album();
            $album->setTitle('Album '.$i);
            $album->setArtist('Artist '.$i);
            $album->setReleaseDate(new \DateTime());
            $album->setAuthor($this->createUser());

            $this->albumService->save($album);
        }

        $result = $this->albumService->getPaginatedList(1);

        $this->assertInstanceOf(PaginationInterface::class, $result);
    }

    /**
     * Test removing favourite.
     */
    public function testToggleFavoriteRemovesExistingFavorite(): void
    {
        $user = $this->createUser();

        $album = new Album();
        $album->setTitle('Fav Album');
        $album->setArtist('Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setAuthor($user);

        $this->entityManager->persist($album);
        $this->entityManager->flush();

        $user->addFavorite($album);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->assertTrue($user->getFavorites()->contains($album));


        $this->albumService->toggleFavorite($album, $user);

        $this->assertFalse($user->getFavorites()->contains($album));
    }

    /**
     * Tests that an existing favorite album is removed when toggling by id.
     */
    public function testToggleFavoriteByIdRemovesExistingFavorite(): void
    {
        $user = $this->createUser();

        $album = new Album();
        $album->setTitle('Test');
        $album->setArtist('Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setAuthor($user);

        $this->entityManager->persist($album);
        $this->entityManager->flush();

        $user->addFavorite($album);
        $this->entityManager->flush();

        $this->assertTrue($user->getFavorites()->contains($album));

        $this->albumService->toggleFavoriteById($album->getId(), $user);

        $this->assertFalse($user->getFavorites()->contains($album));
    }

    /**
     * Tests that toggleFavoriteById throws InvalidArgumentException when album is not found.
     *
     * @throws Exception
     */
    public function testToggleFavoriteByIdThrowsIfAlbumMissing(): void
    {
        $albumRepository = $this->createMock(AlbumRepository::class);
        $albumRepository->method('find')->willReturn(null);

        $albumService = $this->createMock(AlbumService::class);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturn('not found');

        $service = self::getContainer()->get(AlbumService::class);

        $this->expectException(\InvalidArgumentException::class);

        $service->toggleFavoriteById(999, new User());
    }

    /**
     * Test creating a user.
     *
     * @return User The created user
     */
    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('test'.uniqid().'@example.com');
        $user->setPassword('hashed-password'); // IMPORTANT: avoid null constraints
        $user->setUsername('testuser'.uniqid());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
