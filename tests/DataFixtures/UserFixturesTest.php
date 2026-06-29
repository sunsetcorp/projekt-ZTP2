<?php

/**
 * User fixtures tests.
 */

namespace App\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

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


        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }

    /**
     * Test loading the fixtures.
     */
    public function testFixturesLoad(): void
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $this->assertGreaterThanOrEqual(13, count($users));
    }
}
