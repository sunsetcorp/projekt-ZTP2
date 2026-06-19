<?php

/**
 * Category controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class CategoryControllerTests.
 */
class CategoryControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @var string
     */
    public const TEST_ROUTE = '/category';
    /**
     * Test client.
     */
    private KernelBrowser $client;
    /**
     * Entity manager.
     */
    private EntityManagerInterface $entityManager;

    /**
     * Set up tests.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Test if index page is accessible for guests.
     */
    public function testIndexAnonymousAccessible(): void
    {
        $this->client->request('GET', self::TEST_ROUTE);

        $this->assertResponseIsSuccessful(); // 200 OK
    }

    /**
     * Test index page as a user.
     */
    public function testIndexAsUser(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->loginUser($user);
        $this->client->request('GET', self::TEST_ROUTE);

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * Test admin page as an admin.
     */
    public function testIndexAsAdmin(): void
    {
        $user = $this->createUser([
            UserRole::ROLE_USER->value,
            UserRole::ROLE_ADMIN->value,
        ]);

        $this->client->loginUser($user);
        $this->client->request('GET', self::TEST_ROUTE);

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * Test showing a category page.
     */
    public function testShowCategory(): void
    {
        $category = new Category();
        $category->setTitle('Rock');

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $this->client->request('GET', '/category/category/'.$category->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test if creating a category requires an admin.
     */
    public function testCreateRequiresAdmin(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->loginUser($user);
        $this->client->request('GET', '/category/create');

        $this->assertResponseRedirects('/access-denied');
    }

    /**
     * Test for form when creating as an admin.
     */
    public function testCreateAsAdminShowsForm(): void
    {
        $user = $this->createUser([
            UserRole::ROLE_USER->value,
            UserRole::ROLE_ADMIN->value,
        ]);

        $this->client->loginUser($user);
        $this->client->request('GET', '/category/create');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test if editing requires an admin.
     */
    public function testEditRequiresAdmin(): void
    {
        $category = new Category();
        $category->setTitle('Test');

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->loginUser($user);
        $this->client->request('GET', '/category/'.$category->getId().'/edit');

        $this->assertResponseRedirects('/access-denied');
    }

    /**
     * Test if deleting requires an admin.
     */
    public function testDeleteRequiresAdmin(): void
    {
        $category = new Category();
        $category->setTitle('Test');

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->loginUser($user);
        $this->client->request('GET', '/category/'.$category->getId().'/delete');

        $this->assertResponseRedirects('/access-denied');
    }

    /**
     * Test creating a category.
     */
    public function testCreateCategory(): void
    {
        $admin = new User();
        $em = self::getContainer()->get('doctrine')->getManager();

        $admin->setEmail('admin@test.com');
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword('x');

        $em->persist($admin);
        $em->flush();

        $this->client->loginUser($admin);

        $this->client->request('POST', '/category/create', [
            'category' => [
                'title' => 'Test Category',
            ],
        ]);

        $this->assertResponseRedirects();
    }

    /**
     * Test deleting a category.
     */
    public function testDeleteCategory(): void
    {
        $admin = new User();
        $em = self::getContainer()->get('doctrine')->getManager();

        $admin->setEmail('admin'.uniqid().'@test.com');
        $admin->setUsername('admin'.uniqid());
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword('x');

        $category = new Category();
        $category->setTitle('To delete');

        $em->persist($admin);
        $em->persist($category);
        $em->flush();

        $this->client->loginUser($admin);

        $crawler = $this->client->request(
            'GET',
            '/category/'.$category->getId().'/delete'
        );

        $this->assertResponseIsSuccessful();

        $this->client->request('GET', '/category/'.$category->getId().'/delete');

        $this->client->request(
            'POST',
            '/category/'.$category->getId().'/delete',
            [
                'form' => [],
            ]
        );

        $this->assertResponseRedirects('/category');
    }

    /**
     * Creating user helper function.
     *
     * @param array $roles Roles
     *
     * @return User The created user
     */
    private function createUser(array $roles): User
    {
        $container = static::getContainer();

        $user = new User();
        $user->setEmail('test'.uniqid().'@example.com');
        $user->setUsername('test'.uniqid()); // ✅ FIX HERE
        $user->setRoles($roles);

        $hasher = $container->get('security.password_hasher');

        $user->setPassword(
            $hasher->hashPassword($user, 'password')
        );

        $repo = $container->get(UserRepository::class);
        $repo->save($user);

        return $user;
    }
}
