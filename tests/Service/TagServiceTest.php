<?php

/**
 * Tag service tests.
 */

namespace App\Tests\Service;

use App\Repository\AlbumRepository;
use App\Repository\TagRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Tag;
use App\Service\TagService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class TagServiceTest.
 */
class TagServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private EntityManagerInterface $entityManager;
    /**
     * Tag service.
     */
    private TagService $tagService;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->tagService = $container->get(TagService::class);
    }

    /**
     * Test saving.
     */
    public function testSave(): void
    {
        $tag = new Tag();
        $tag->setTitle('rock');

        $this->tagService->save($tag);

        $result = $this->entityManager
            ->createQueryBuilder()
            ->select('t')
            ->from(Tag::class, 't')
            ->where('t.title = :title')
            ->setParameter('title', 'rock')
            ->getQuery()
            ->getSingleResult();

        $this->assertSame($tag->getTitle(), $result->getTitle());
    }

    /**
     * Test deleting.
     */
    public function testDelete(): void
    {
        $tag = new Tag();
        $tag->setTitle('to-delete');

        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        $id = $tag->getId();

        $this->tagService->delete($tag);

        $result = $this->entityManager
            ->createQueryBuilder()
            ->select('t')
            ->from(Tag::class, 't')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($result);
    }

    /**
     * Test finding by title.
     */
    public function testFindOneByTitle(): void
    {
        $tag = new Tag();
        $tag->setTitle('rock');

        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        $result = $this->tagService->findOneByTitle('rock');

        $this->assertInstanceOf(Tag::class, $result);
        $this->assertSame('rock', $result->getTitle());
    }

    /**
     * Test if tag can be deleted.
     */
    public function testCanBeDeleted(): void
    {
        $tag = new Tag();
        $tag->setTitle('unused');

        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        $result = $this->tagService->canBeDeleted($tag);

        $this->assertIsBool($result);
    }

    /**
     * Test getting paginated list.
     */
    public function testGetPaginatedList(): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $tag = new Tag();
            $tag->setTitle('tag-'.$i);
            $this->tagService->save($tag);
        }

        $result = $this->tagService->getPaginatedList(1);

        $this->assertInstanceOf(PaginationInterface::class, $result);
        $this->assertGreaterThanOrEqual(3, $result->count());
    }

    /**
     * Test if tag cannot be deleted.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCanBeDeletedReturnsFalseWhenRepositoryThrowsException(): void
    {
        $tag = new Tag();
        $tag->setTitle('broken-tag');

        $albumRepository = $this->createMock(AlbumRepository::class);

        $albumRepository->expects($this->once())
            ->method('countByTag')
            ->with($tag)
            ->willThrowException(new \Exception('Database error'));

        $tagRepository = $this->createMock(TagRepository::class);

        $paginator = $this->createMock(PaginatorInterface::class);

        $service = new TagService(
            $tagRepository,
            $albumRepository,
            $paginator
        );

        $result = $service->canBeDeleted($tag);

        $this->assertFalse($result);
    }
}
