<?php

/**
 * User fixtures tests.
 */

namespace App\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\Loader;
use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class UserFixturesTest.
 */
class UserFixturesTest extends KernelTestCase
{
    /**
     * Entity Manager.
     */
    private EntityManagerInterface $entityManager;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()
            ->get('doctrine')
            ->getManager();

        $fixtures = self::getContainer()->get(UserFixtures::class);

        $loader = new Loader();
        $loader->addFixture($fixtures);
    }

    /**
     * Test loading the fixtures.
     */
    public function testFixturesLoad(): void
    {

        $em = self::getContainer()->get('doctrine')->getManager();

        $users = $em->getRepository(User::class)->findAll();

        $this->assertGreaterThanOrEqual(13, count($users));
    }
}
