<?php

/**
 * Category Fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class CategoryFixtures.
 */
class CategoryFixtures extends Fixture
{
    public const CAT_1 = 'cat-1';

    /**
     * Load category fixtures function.
     *
     * @param ObjectManager $manager object manager
     */
    public function load(ObjectManager $manager): void
    {
        $cat = new Category();
        $cat->setTitle('Electronic');
        $cat->setSlug('electronic');
        $cat->setCreatedAt(new \DateTimeImmutable());
        $cat->setUpdatedAt(new \DateTimeImmutable());

        $manager->persist($cat);
        $this->addReference(self::CAT_1, $cat);

        $manager->flush();
    }
}
