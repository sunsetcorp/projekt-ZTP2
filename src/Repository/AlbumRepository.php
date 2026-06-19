<?php

/**
 * Album repository.
 */

namespace App\Repository;

use App\Entity\Album;
use App\Entity\Category;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 *  Class AlbumRepository.
 *
 * @method Album|null find($id, $lockMode = null, $lockVersion = null)
 * @method Album|null findOneBy(array $criteria, array $orderBy = null)
 * @method Album[]    findAll()
 * @method Album[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Album>
 *
 * @psalm-suppress LessSpecificImplementedReturnType
 */
class AlbumRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }

    /**
     * Query all albums.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->orderBy('album.releaseDate', 'DESC');
    }

    /**
     * Find albums by tag.
     *
     * @param Tag $tag tag of an album to find
     *
     * @return Album[]
     */
    public function findByTag(Tag $tag): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.tags', 't')
            ->where('t.id = :tag')
            ->setParameter('tag', $tag->getId())->getQuery()
            ->getResult();
    }

    /**
     * Count albums by tag.
     *
     * @param Tag $tag Tag entity
     *
     * @return int Number of albums in category
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByTag(Tag $tag): int
    {
        $qb = $this->createQueryBuilder('album');

        return (int) $qb->select($qb->expr()->countDistinct('album.id'))
            ->innerJoin('album.tags', 't')
            ->where('t.id = :tagId')
            ->setParameter('tagId', $tag->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Save entity.
     *
     * @param Album $album Album entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Album $album): void
    {
        $em = $this->getEntityManager();

        $em->persist($album);
        $em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Album $album Album entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Album $album): void
    {
        $em = $this->getEntityManager();

        $em->remove($album);
        $em->flush();
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('album');
    }
}
