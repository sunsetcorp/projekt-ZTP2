<?php

/**
 * Admin service tests.
 */

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\AdminService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class AdminServiceTest.
 */
class AdminServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private EntityManagerInterface $entityManager;
    /**
     * Admin service.
     */
    private AdminService $adminService;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->adminService = $container->get(AdminService::class);
    }

    /**
     * Test updating the user.
     */
    public function testUpdateUser(): void
    {
        $user = $this->createUser();

        $this->adminService->updateUser($user);

        $result = $this->entityManager
            ->getRepository(User::class)
            ->find($user->getId());

        $this->assertSame($user->getEmail(), $result->getEmail());
    }

    /**
     * Test counting other admins.
     */
    public function testCountOtherAdmins(): void
    {
        $user = $this->createUser();

        $count = $this->adminService->countOtherAdmins($user);

        $this->assertIsInt($count);
    }

    /**
     * Test updating user password.
     */
    public function testUpdateUserPassword(): void
    {
        $user = $this->createUser();

        $this->adminService->updateUserPassword($user, 'newpassword');

        $updated = $this->entityManager
            ->getRepository(User::class)
            ->find($user->getId());

        $this->assertNotSame('newpassword', $updated->getPassword());
    }

    /**
     * Test getting paginated users.
     */
    public function testGetPaginatedUsers(): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $this->createUser();
        }

        $result = $this->adminService->getPaginatedUsers(1, 10);

        $this->assertInstanceOf(PaginationInterface::class, $result);
    }

    /**
     * Test getting all users.
     */
    public function testGetAllUsers(): void
    {
        $this->createUser();
        $this->createUser();

        $result = $this->adminService->getAllUsers();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /**
     * Test creating a user.
     *
     * @return User The created user
     */
    private function createUser(): User
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('admin'.uniqid().'@test.com');
        $user->setUsername('admin'.uniqid());
        $user->setPassword($hasher->hashPassword($user, 'plain'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
