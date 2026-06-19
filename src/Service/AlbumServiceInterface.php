<?php

/**
 * Album service interface.
 */

namespace App\Service;

use App\Entity\Album;
use App\Entity\Tag;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface AlbumServiceInterface.
 */
interface AlbumServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Album $album Album entity
     */
    public function save(Album $album): void;

    /**
     * Delete entity.
     *
     * @param Album $album Album entity
     */
    public function delete(Album $album): void;

    /**
     *  Remove favourited entity.
     *
     * @param Album $id   Album ID
     * @param User  $user User entity
     */
    public function removeFavorite(int $id, $user): void;

    /**
     * Albums by tag action.
     *
     * @param Tag $tag Tag entity
     */
    public function getAlbumsByTag(Tag $tag): array;

    /**
     * Add favourite album.
     *
     * @param Album $album Album entity
     * @param User  $user  User entity
     */
    public function toggleFavorite(Album $album, $user): void;
}
