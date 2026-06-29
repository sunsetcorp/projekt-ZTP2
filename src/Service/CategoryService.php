<?php

/**
 * Category service.
 */

namespace App\Service;

use App\Repository\CategoryRepository;
use App\Repository\AlbumRepository;
use App\Entity\Category;
use Symfony\Contracts\Translation\TranslatorInterface;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Class CategoryService.
 *
 * Service class for managing categories.
 */
class CategoryService implements CategoryServiceInterface
{
    /**
     * CategoryService constructor.
     *
     * @param AlbumRepository     $albumRepository    The album repository
     * @param PaginatorInterface  $paginator          The paginator
     * @param CategoryRepository  $categoryRepository The category repository
     * @param TranslatorInterface $translator         The translator
     */
    public function __construct(private readonly AlbumRepository $albumRepository, private readonly PaginatorInterface $paginator, private readonly CategoryRepository $categoryRepository, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Get paginated list of categories.
     *
     * @param int $page The page number
     *
     * @return PaginationInterface The paginated list of categories
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate($this->categoryRepository->queryAll(), $page, 10);
    }

    /**
     * Save entity.
     *
     * @param Category $category Category entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    /**
     * Save entity.
     *
     * @param Category $category Category entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Category $category): void
    {
        $this->categoryRepository->save($category);
    }

    /**
     * Delete category entity.
     *
     * @param Category $category The category entity to delete
     */
    public function delete(Category $category): void
    {
        $this->categoryRepository->delete($category);
    }

    /**
     * Can Category be deleted?
     *
     * @param Category $category Category entity
     *
     * @return bool Result
     */
    public function canBeDeleted(Category $category): bool
    {
        return $this->categoryRepository->canBeDeleted($category);
    }

    /**
     * Get albums that belong in given category.
     *
     * @param int $id The id of the category
     *
     * @return array Array of albums in category
     */
    public function getCategoryWithAlbums(int $id): array
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            throw new \InvalidArgumentException($this->translator->trans('message.category_not_found'));
        }

        $albums = $this->albumRepository->findBy(['category' => $category]);

        return [
            'category' => $category,
            'albums' => $albums,
        ];
    }

    /**
     * Get all categories.
     *
     * @return array Array of categories
     */
    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAll();
    }
}
