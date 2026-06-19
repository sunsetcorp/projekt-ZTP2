<?php

/**
 * Tag repository.
 */

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    /**
     * TagRepository constructor.
     *
     * @param ManagerRegistry        $registry The registry service for managing entities
     * @param EntityManagerInterface $em       The entity manager interface
     */
    public function __construct(ManagerRegistry $registry, private readonly EntityManagerInterface $em)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Get a query builder that selects all tags with partial fields.
     *
     * @return QueryBuilder the query builder for selecting all tags
     */
    public function queryAll(): QueryBuilder
    {
        return $this->createQueryBuilder('tag')
            ->select('partial tag.{id, createdAt, updatedAt, title}')
            ->orderBy('tag.updatedAt', 'DESC');
    }

    /**
     * Save a tag entity.
     *
     * @param Tag $tag The tag entity to save
     */
    public function save(Tag $tag): void
    {
        if (null === $tag->getId()) {
            $tag->setCreatedAt(new \DateTimeImmutable());
        }
        $tag->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($tag);
        $this->em->flush();
    }

    /**
     * Remove a tag entity.
     *
     * @param Tag $tag The tag entity to remove
     */
    public function remove(Tag $tag): void
    {
        $em = $this->getEntityManager();

        $em->remove($tag);
        $em->flush();
    }

    /**
     * Find a tag entity by its title.
     *
     * @param string $title The title of the tag to find
     *
     * @return Tag|null The tag entity, if found
     */
    public function findOneByTitle(string $title): ?Tag
    {
        return $this->findOneBy(['title' => $title]);
    }
}
