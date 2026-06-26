<?php

/**
 * Tag Fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class TagFixtures.
 */
class TagFixtures extends Fixture
{
    public const TAG_1 = 'tag-1';
    public const TAG_2 = 'tag-2';

    /**
     * Load tag fixtures.
     *
     * @param ObjectManager $manager object manager
     */
    public function load(ObjectManager $manager): void
    {
        $tag1 = new Tag();
        $tag1->setTitle('LP');
        $tag1->setSlug('lp');
        $tag1->setCreatedAt(new \DateTimeImmutable());
        $tag1->setUpdatedAt(new \DateTimeImmutable());

        $manager->persist($tag1);
        $this->addReference(self::TAG_1, $tag1);

        $tag2 = new Tag();
        $tag2->setTitle('EP');
        $tag2->setSlug('ep');
        $tag2->setCreatedAt(new \DateTimeImmutable());
        $tag2->setUpdatedAt(new \DateTimeImmutable());

        $manager->persist($tag2);
        $this->addReference(self::TAG_2, $tag2);

        $manager->flush();
    }
}
