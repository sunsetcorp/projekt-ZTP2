<?php

/**
 * Rating repository.
 */

namespace App\Repository;

use App\Entity\Rating;
use App\Entity\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rating>
 */
class RatingRepository extends ServiceEntityRepository
{
    /**
     * Constructor for the User Repository.
     *
     * @param ManagerRegistry $registry the ManagerRegistry service instance managing entities
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    /**
     * Gets average score for an album.
     *
     * @param Album $album The album
     *
     * @return float Average
     */
    public function getAverageForAlbum(Album $album): float
    {
        return (float) $this->createQueryBuilder('r')
            ->select('AVG(r.value)')
            ->where('r.album = :album')
            ->setParameter('album', $album)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Finds 50 top-rated albums.
     *
     * @param int $limit Limit of albums
     *
     * @return Album[]
     */
    public function findTopRatedAlbums(int $limit = 50): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('a, AVG(r.value) AS averageRating')
            ->from(Album::class, 'a')
            ->join(Rating::class, 'r', 'WITH', 'r.album = a')
            ->groupBy('a.id')
            ->orderBy('averageRating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     *  Saving the rating.
     *
     * @param Rating $rating The rating to be saved
     */
    public function save(Rating $rating): void
    {
        $this->getEntityManager()->persist($rating);
        $this->getEntityManager()->flush();
    }
}
