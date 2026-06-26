<?php

/**
 * Admin controller tests.
 */

namespace App\Tests\Controller;

use App\Controller\AdminController;
use Doctrine\ORM\Exception\ORMException;
use App\Entity\User;
use App\Entity\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AdminControllerTest.
 */
class AdminControllerTest extends WebTestCase
{
    /**
     * Test client.
     */
    private KernelBrowser $client;
    /**
     * Entity manager.
     */
    private EntityManagerInterface $em;

    /**
     * Set up tests.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        new \ReflectionClass(AdminController::class);
    }

    /**
     * Test if only admin can access user list.
     */
    public function testAdminUserListRequiresAdmin(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->loginUser($user);
        $this->client->request('GET', '/admin/users');

        $this->assertResponseRedirects('/access-denied');
    }

    /**
     * Test whether admin can access the user list.
     */
    public function testAdminUserListAsAdmin(): void
    {
        $admin = $this->createUser([
            UserRole::ROLE_USER->value,
            UserRole::ROLE_ADMIN->value,
        ]);

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/users');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test user list pagination.
     */
    public function testAdminUserListPagination(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/users?page=1');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test edits on the user list.
     */
    public function testEditUserPageAsAdmin(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/users/edit/'.$user->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test if blocked admin cannot make edits on user list.
     */
    public function testBlockedAdminCannotEditUsers(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value], true);
        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/users/edit/'.$user->getId());

        $this->assertResponseRedirects('/admin/users');
    }

    /**
     * Test if admin can block a user.
     *
     * @throws ORMException
     */
    public function testAdminCanBlockUser(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/users/block/'.$user->getId());

        $this->assertResponseRedirects('/admin/users');

        $this->em->refresh($user);
        $this->assertTrue($user->isBlocked());
    }

    /**
     * Test whether admin is not able to block themselves.
     */
    public function testAdminCannotBlockSelf(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/users/block/'.$admin->getId());

        $this->assertResponseRedirects('/admin/users');
    }

    /**
     * Test whether blocked admin cannot block anyone else.
     */
    public function testBlockedAdminCannotBlockOthers(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value], true);
        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/users/block/'.$user->getId());

        $this->assertResponseRedirects('/admin/users');
    }

    /**
     * Test if admin can unblock user.
     *
     * @throws ORMException
     */
    public function testAdminCanUnblockUser(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $user = $this->createUser([UserRole::ROLE_USER->value], true);

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/users/unblock/'.$user->getId());

        $this->assertResponseRedirects('/admin/users');

        $this->em->refresh($user);
        $this->assertFalse($user->isBlocked());
    }

    /**
     * Test if blocked admin cannot unblock themselves.
     */
    public function testAdminCannotUnblockSelf(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/users/unblock/'.$admin->getId());

        $this->assertResponseRedirects('/admin/users');
    }

    /**
     * Test so normal users are denied form blocking anyone.
     */
    public function testUserWithoutAdminRoleDenied(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $user2block = $this->createUser([UserRole::ROLE_USER->value]);
        $this->client->loginUser($user);
        $this->client->request('GET', '/admin/users/block/'.$user2block->getId());

        $this->assertResponseRedirects('/access-denied');
    }

    /**
     * Test repository saves.
     */
    public function testAdminEditTriggersRepositorySave(): void
    {
        $client = $this->client;

        $admin = $this->createUser([
            UserRole::ROLE_ADMIN->value,
        ]);

        $target = $this->createUser([
            UserRole::ROLE_USER->value,
        ]);

        $client->loginUser($admin);

        $crawler = $client->request(
            'GET',
            '/admin/users/edit/'.$target->getId()
        );

        $form = $crawler->filter('[data-testid="save-user"]')->form();

        $client->submit($form, [
            'user_edit' => [
                'roles' => [UserRole::ROLE_USER->value],
            ],
        ]);

        $this->assertResponseRedirects();
    }

    /**
     * Test for creating users.
     *
     * @param array $roles   Roles
     * @param bool  $blocked Blocked
     *
     * @return User The created blocked user
     */
    private function createUser(array $roles, bool $blocked = false): User
    {
        $user = new User();
        $user->setEmail('test'.uniqid().'@example.com');
        $user->setUsername('user'.uniqid());
        $user->setRoles($roles);
        $user->setIsBlocked($blocked);
        $user->setPassword('dummy');

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
