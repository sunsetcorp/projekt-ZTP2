<?php

/**
 * Category service tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Service\CategoryService;
use App\Service\CategoryServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CategoryServiceTest.
 */
class CategoryServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager = null;
    /**
     * Category service.
     */
    private ?CategoryServiceInterface $categoryService = null;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->entityManager = $container->get('doctrine.orm.entity_manager');

        $this->categoryService = $container->get(CategoryService::class);
    }

    /**
     * Test saving.
     */
    public function testSave(): void
    {
        $category = new Category();
        $category->setTitle('Rock');

        $this->categoryService->save($category);

        $savedCategory = $this->entityManager
            ->createQueryBuilder()
            ->select('c')
            ->from(Category::class, 'c')
            ->where('c.id = :id')
            ->setParameter('id', $category->getId(), Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNotNull($savedCategory);

        $this->assertEquals(
            'Rock',
            $savedCategory->getTitle()
        );
    }

    /**
     * Test deleting.
     */
    public function testDelete(): void
    {
        $category = new Category();
        $category->setTitle('Jazz');

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $categoryId = $category->getId();

        $this->categoryService->delete($category);

        $deletedCategory = $this->entityManager
            ->createQueryBuilder()
            ->select('c')
            ->from(Category::class, 'c')
            ->where('c.id = :id')
            ->setParameter('id', $categoryId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($deletedCategory);
    }

    /**
     * Test can be deleted if empty.
     */
    public function testCanBeDeletedReturnsTrueForUnusedCategory(): void
    {
        $category = new Category();
        $category->setTitle('Unused Category');

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $result = $this->categoryService->canBeDeleted($category);

        $this->assertTrue($result);
    }

    /**
     * Test getting a paginated list.
     */
    public function testGetPaginatedList(): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $category = new Category();
            $category->setTitle('Category '.$i);

            $this->entityManager->persist($category);
        }

        $this->entityManager->flush();

        $result = $this->categoryService->getPaginatedList(1);

        $this->assertGreaterThanOrEqual(
            3,
            $result->count()
        );
    }

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager?->close();

        $this->entityManager = null;
        $this->categoryService = null;
    }

    /**
     * Test updating timestamp.
     */
    public function testSaveExistingCategoryUpdatesTimestamp(): void
    {
        $service = self::getContainer()->get(
            CategoryServiceInterface::class
        );

        $category = new Category();
        $category->setTitle('Test');

        $service->save($category);

        $oldUpdatedAt = $category->getUpdatedAt();

        sleep(1);

        $category->setTitle('Changed');

        $service->save($category);

        $this->assertGreaterThan(
            $oldUpdatedAt->getTimestamp(),
            $category->getUpdatedAt()->getTimestamp()
        );
    }
}
