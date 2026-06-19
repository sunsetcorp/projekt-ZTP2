<?php

/**
 * Cover repository.
 */

namespace App\Repository;

use App\Entity\Cover;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cover>
 */
class CoverRepository extends ServiceEntityRepository
{
    /**
     * Constructor for the User Repository.
     *
     * @param ManagerRegistry $registry the ManagerRegistry service instance managing entities
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cover::class);
    }

    /**
     * Save entity.
     *
     * @param Cover $cover The album cover
     */
    public function save(Cover $cover): void
    {
        $em = $this->getEntityManager();

        $em->persist($cover);
        $em->flush();
    }
}
