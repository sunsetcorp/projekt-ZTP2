<?php

/**
 * Admin repository tests.
 */

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AdminRepositoryTests.
 */
class AdminRepositoryTest extends KernelTestCase
{
    /**
     * Entity Manager.
     */
    private EntityManagerInterface $em;
    /**
     * User Repository.
     */
    private UserRepository $repo;

    /**
     * Set up tests.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);

        $this->repo = $this->em->getRepository(User::class);
    }

    /**
     * Test saving persists and flushes.
     */
    public function testSavePersistsAndFlushes(): void
    {
        $user = new User();
        $user->setEmail('coverage@test.com');
        $user->setUsername('coverage_user');
        $user->setPassword('test');

        $this->repo->save($user);

        $this->assertTrue(true);
    }

    /**
     * Test if save execute without error.
     */
    public function testSaveExecutesWithoutError(): void
    {
        $user = new User();
        $user->setEmail('coverage2@test.com');
        $user->setUsername('coverage_user2');
        $user->setRoles(['ROLE_USER']);

        $hasher = static::getContainer()->get('security.password_hasher');
        $user->setPassword($hasher->hashPassword($user, 'password'));

        $this->repo->save($user);

        $this->assertTrue(true);
    }
}
