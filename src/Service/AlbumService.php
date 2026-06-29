<?php

/**
 * Album service.
 */

namespace App\Service;

use App\Entity\Album;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Cover;
use App\Repository\CommentRepository;
use App\Repository\CoverRepository;
use App\Repository\AlbumRepository;
use App\Repository\RatingRepository;
use App\Repository\TagRepository;
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
     * @param AlbumRepository     $albumRepository   Album repository
     * @param PaginatorInterface  $paginator         Paginator
     * @param TranslatorInterface $translator        The translator
     * @param CoverRepository     $coverRepository   Cover repository
     * @param CommentRepository   $commentRepository Comment repository
     * @param RatingRepository    $ratingRepository  Rating repository
     * @param TagRepository       $tagRepository     Tag repository
     */
    public function __construct(private readonly AlbumRepository $albumRepository, private readonly PaginatorInterface $paginator, private readonly TranslatorInterface $translator, private readonly CoverRepository $coverRepository, private readonly CommentRepository $commentRepository, private readonly RatingRepository $ratingRepository, private readonly TagRepository $tagRepository)
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
            throw new \InvalidArgumentException($this->translator->trans('message.album_not_found'));
        }

        if ($user->getFavorites()->contains($album)) {
            $user->removeFavorite($album);
            $this->albumRepository->flush();
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

        $this->albumRepository->saveUser($user);
    }

    /**
     * Toggle favourite album by id.
     *
     * @param int  $id   the id of an album
     * @param User $user the user
     *
     * @return string Message to display to user
     */
    public function toggleFavoriteById(int $id, User $user): string
    {
        $album = $this->albumRepository->find($id);

        if (!$album) {
            throw new \InvalidArgumentException($this->translator->trans('message.album_not_found'));
        }

        if ($user->getFavorites()->contains($album)) {
            $user->removeFavorite($album);
            $message = 'message.removedFav';
        } else {
            $user->addFavorite($album);
            $message = 'message.addedFav';
        }

        $this->albumRepository->save($album);

        return $message;
    }

    /**
     * Get album details.
     *
     * @param Album $album the album
     *
     * @return Album The album details to render the page
     */
    public function getDetailedAlbum(Album $album): Album
    {
        return $this->albumRepository->findDetailedAlbum($album);
    }

    /**
     * Get comments.
     *
     * @param Album $album The album
     *
     * @return array Array of comments to display
     */
    public function getComments(Album $album): array
    {
        return $this->commentRepository->findBy(['album' => $album]);
    }

    /**
     * Get the album cover.
     *
     * @param Album $album The album to get cover for
     *
     * @return Cover|null Get the cover for album
     */
    public function getCover(Album $album): ?Cover
    {
        return $this->coverRepository->findOneByAlbum($album);
    }

    /**
     * Get user rating.
     *
     * @param Album     $album the album
     * @param User|null $user  the user
     *
     * @return int|null The user rating of the album
     */
    public function getUserRating(Album $album, ?User $user): ?int
    {
        if (!$user) {
            return null;
        }

        $rating = $this->ratingRepository->findOneBy([
            'album' => $album,
            'user' => $user,
        ]);

        return $rating?->getValue();
    }

    /**
     * Get the average rating for an album.
     *
     * @param Album $album the album
     *
     * @return float The average rating of the album
     */
    public function getAverageRating(Album $album): float
    {
        return $this->ratingRepository->getAverageForAlbum($album);
    }

    /**
     *  Gets top-rated albums.
     *
     * @return array Array of rated albums
     */
    public function getTopRatedAlbums(): array
    {
        return $this->ratingRepository->findTopRatedAlbums();
    }

    /**
     * Find the tag by id.
     *
     * @param int $id The tag id
     *
     * @return Tag Tag by the given id
     */
    public function getTagById(int $id): Tag
    {
        $tag = $this->tagRepository->find($id);

        if (!$tag) {
            throw new \InvalidArgumentException($this->translator->trans('message.tagdoesnotexist'));
        }

        return $tag;
    }
}
