<?php

/**
 * Tag service.
 */

namespace App\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Repository\AlbumRepository;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Class TagService.
 *
 * Service class handling operations related to tags.
 */
class TagService implements TagServiceInterface
{
    /**
     * TagService constructor.
     *
     * @param TagRepository      $tagRepository   The repository for Tag entities
     * @param AlbumRepository    $albumRepository The repository for Album entities
     * @param PaginatorInterface $paginator       The paginator interface for pagination
     */
    public function __construct(private readonly TagRepository $tagRepository, private readonly AlbumRepository $albumRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Retrieves a paginated list of tags.
     *
     * @param int $page The current page number
     *
     * @return PaginationInterface Paginated list of tags
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate($this->tagRepository->queryAll(), $page, 10);
    }

    /**
     * Saves a tag entity.
     *
     * @param Tag $tag The tag entity to be saved
     */
    public function save(Tag $tag): void
    {
        $this->tagRepository->save($tag);
    }

    /**
     * Deletes a tag entity.
     *
     * @param Tag $tag The tag entity to be deleted
     */
    public function delete(Tag $tag): void
    {
        $this->tagRepository->remove($tag);
    }

    /**
     * Checks if a tag can be deleted.
     *
     * @param Tag $tag The tag entity to check
     *
     * @return bool Returns true if the tag can be deleted, false otherwise
     */
    public function canBeDeleted(Tag $tag): bool
    {
        try {
            $result = $this->albumRepository->countByTag($tag);

            return 0 === $result;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Finds a tag by its title.
     *
     * @param string $title The title of the tag to find
     *
     * @return Tag|null The found tag entity or null if not found
     */
    public function findOneByTitle(string $title): ?Tag
    {
        return $this->tagRepository->findOneByTitle($title);
    }
}
