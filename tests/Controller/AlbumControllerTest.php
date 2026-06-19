<?php

/**
 * Album controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Album;
use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Entity\Tag;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AlbumControllerTest.
 */
class AlbumControllerTest extends WebTestCase
{
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
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * Test if index page is accessible for guests.
     */
    public function testIndexAnonymousAccessible(): void
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    /**Test filtering on index page.
     *
     * @return void
     */
    public function testIndexWithFilters(): void
    {
        $this->client->request('GET', '/?phrase=rock&page=2&category=1');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test showing album page as a guest.
     */
    public function testShowAlbumAnonymousAccessible(): void
    {
        $author = $this->createUser([UserRole::ROLE_USER->value]);
        $album = $this->createAlbum($author);

        $this->client->request('GET', '/'.$album->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test if creating an entry requires an admin.
     */
    public function testCreateRequiresAdmin(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->loginUser($user);
        $this->client->request('GET', '/create');

        $this->assertResponseRedirects('/access-denied');
    }

    /**
     * Test if it is possible to create an entry as an admin.
     */
    public function testCreateAsAdmin(): void
    {
        $admin = $this->createUser([
            UserRole::ROLE_USER->value,
            UserRole::ROLE_ADMIN->value,
        ]);

        $this->client->loginUser($admin);
        $this->client->request('GET', '/create');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test if editing requires an admin.
     */
    public function testEditRequiresAdmin(): void
    {
        $author = $this->createUser([UserRole::ROLE_USER->value]);
        $album = $this->createAlbum($author);

        $this->client->loginUser($author);
        $this->client->request('GET', '/'.$album->getId().'/edit');

        $this->assertResponseRedirects('/access-denied');
    }

    /**
     * Test editing as an admin.
     */
    public function testEditAsAdmin(): void
    {
        $author = $this->createUser([UserRole::ROLE_USER->value]);
        $album = $this->createAlbum($author);

        $admin = $this->createUser([
            UserRole::ROLE_USER->value,
            UserRole::ROLE_ADMIN->value,
        ]);

        $this->client->loginUser($admin);
        $this->client->request('GET', '/'.$album->getId().'/edit');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test when tag is not found.
     */
    public function testAlbumsByTagNotFound(): void
    {
        $this->client->request('GET', '/albums/tag/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Test finding albums by tags.
     */
    public function testAlbumsByTagSuccess(): void
    {
        $tag = new Tag();
        $tag->setTitle('Rock');

        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        $this->client->request('GET', '/albums/tag/'.$tag->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test adding favourite album.
     */
    public function testFavoriteAlbum(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $album = $this->createAlbum($user);

        $this->client->loginUser($user);
        $this->client->request('POST', '/album/'.$album->getId().'/favorite');

        $this->assertResponseRedirects();
    }

    /**
     * Test removing a favourite album.
     */
    public function testRemoveFavorite(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $album = $this->createAlbum($user);

        $this->client->loginUser($user);
        $this->client->request('GET', '/album/'.$album->getId().'/remove-favorite');

        $this->assertResponseRedirects();
    }

    /**
     * Test rendering album page for a user.
     */
    public function testShowAlbumAsUser(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $album = $this->createAlbum($user);

        $this->client->loginUser($user);
        $this->client->request('GET', '/'.$album->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test creating album by an admin.
     */
    public function testCreatePostSuccessAsAdmin(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);

        $this->client->loginUser($admin);
        $category = $this->entityManager
            ->getRepository(Category::class)
            ->findOneBy([]);

        self::assertNotNull($category);
        $crawler = $this->client->request('GET', '/create');

        $form = $crawler->filter('[data-testid="submit"]')->form();

        $form['album[title]'] = 'New Album';
        $form['album[artist]'] = 'Artist';
        $form['album[releaseDate]'] = '2026-05-31';
        $form['album[category]'] = (string) $category->getId();
        $this->client->submit($form);

        $this->assertResponseRedirects('/');
    }

    /**
     * Test editing album as an admin.
     */
    public function testEditPostSuccess(): void
    {
        $admin = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $album = $this->createAlbum($admin);

        $this->client->loginUser($admin);
        $category = $this->entityManager
            ->getRepository(Category::class)
            ->findOneBy([]);

        self::assertNotNull($category);

        $this->client->request('POST', '/'.$album->getId().'/edit', [
            'album' => [
                'title' => 'Updated Album',
                'artist' => 'Updated Artist',
                'releaseDate' => '2026-05-31',
                'category' => $category->getId(),
            ],
        ]);

        $this->assertResponseRedirects('/');
    }

    /**
     * Test removing favourite form an invalid album.
     */
    public function testRemoveFavoriteInvalidAlbum(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $this->client->loginUser($user);

        $this->client->request('GET', '/album/999999/remove-favorite');

        $this->assertResponseRedirects();
    }

    /**
     * Test redirection for blocked user.
     */
    public function testBlockedUserRedirects(): void
    {
        $user = $this->createUser([UserRole::ROLE_USER->value]);

        $user->setIsBlocked(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);

        $this->client->request('GET', '/create');

        $this->assertResponseRedirects();
    }

    /**
     * Test top-rated albums chart.
     */
    public function testTopRatedPage(): void
    {
        $this->client->request('GET', '/top-rated');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test album not found page.
     */
    public function testShowAlbumNotFound(): void
    {
        $this->client->request('GET', '/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Test for creating users.
     *
     * @param array $roles Roles
     *
     * @return User The created user
     */
    private function createUser(array $roles): User
    {
        $user = new User();
        $user->setEmail('test'.uniqid().'@example.com');
        $user->setUsername('test'.uniqid());
        $user->setRoles($roles);

        $hasher = static::getContainer()->get('security.password_hasher');
        $user->setPassword($hasher->hashPassword($user, 'password'));

        $repo = static::getContainer()->get(UserRepository::class);
        $repo->save($user);

        return $user;
    }

    /**
     * Test for creating albums.
     *
     * @param User $author Author
     *
     * @return Album The created album
     */
    private function createAlbum(User $author): Album
    {
        $category = new Category();
        $category->setTitle('Rock');

        $this->entityManager->persist($category);

        $album = new Album();
        $album->setTitle('Test Album');
        $album->setArtist('Test Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setCategory($category);
        $album->setAuthor($author);
        $album->setSlug('test-album-'.uniqid());

        $this->entityManager->persist($album);
        $this->entityManager->flush();

        return $album;
    }
}
