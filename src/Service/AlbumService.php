<?php

/**
 * Album service.
 */

namespace App\Service;

use App\Entity\Album;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AlbumService.
 */
class AlbumService implements AlbumServiceInterface
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @varant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param AlbumRepository        $albumRepository   Album repository
     * @param PaginatorInterface     $paginator         Paginator
     * @param EntityManagerInterface $entityManager     The entity manager
     * @param TranslatorInterface    $translator        The translator
     * @param CommentRepository      $commentRepository Comment repository
     */
    public function __construct(private readonly AlbumRepository $albumRepository, private readonly PaginatorInterface $paginator, private readonly EntityManagerInterface $entityManager, private readonly TranslatorInterface $translator, CommentRepository $commentRepository)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int     $page       Page number
     * @param int     $categoryId Category ID
     * @param ?string $phrase     Phrase
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, int $categoryId = 0, ?string $phrase = null): PaginationInterface
    {
        $queryBuilder = $this->albumRepository->createQueryBuilder('a')
            ->orderBy('a.releaseDate', 'DESC')
            ->leftJoin('a.category', 'c')
            ->addSelect('c');

        if (0 !== $categoryId) {
            $queryBuilder
                ->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }
        if ($phrase) {
            $queryBuilder
                ->leftJoin('a.tags', 't')
                ->addSelect('t')
                ->andWhere(
                    'LOWER(a.title) LIKE LOWER(:phrase)
            OR LOWER(a.artist) LIKE LOWER(:phrase)
            OR LOWER(t.title) LIKE LOWER(:phrase)'
                )
                ->setParameter('phrase', '%'.$phrase.'%');
        }

        return $this->paginator->paginate($queryBuilder, $page, self::PAGINATOR_ITEMS_PER_PAGE);
    }

    /**
     * Save entity.
     *
     * @param Album $album Album entity
     */
    public function save(Album $album): void
    {
        $this->albumRepository->save($album);
    }

    /**
     * Delete entity.
     *
     * @param Album $album Album entity
     */
    public function delete(Album $album): void
    {
        $this->albumRepository->delete($album);
    }

    /**
     *  Remove favourited entity.
     *
     * @param int  $id   Album ID
     * @param User $user User entity
     *
     * @throws \InvalidArgumentException When album is not found
     */
    public function removeFavorite(int $id, $user): void
    {
        $album = $this->albumRepository->find($id);

        if (!$album) {
            throw new \InvalidArgumentException('Album not found');
        }

        if ($user->getFavorites()->contains($album)) {
            $user->removeFavorite($album);
            $this->entityManager->flush();
        }
    }

    /**
     * Albums by tag action.
     *
     * @param Tag $tag Tag entity
     *
     * @return Album[] Array of albums associated with the tag
     */
    public function getAlbumsByTag(Tag $tag): array
    {
        return $this->albumRepository->findByTag($tag);
    }

    /**
     * Add favourite album.
     *
     * @param Album $album Album entity
     * @param User  $user  User entity
     */
    public function toggleFavorite(Album $album, $user): void
    {
        if ($user->getFavorites()->contains($album)) {
            $user->removeFavorite($album);
            $message = $this->translator->trans('message.removedFav');
        } else {
            $user->addFavorite($album);
            $message = $this->translator->trans('message.addedFav');
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
