<?php

/**
 * Rating controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Album;
use App\Entity\User;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class RatingControllerTest.
 */
class RatingControllerTest extends WebTestCase
{
    /**
     * Entity manager.
     */
    private EntityManagerInterface $entityManager;
    /**
     * Test client.
     */
    private KernelBrowser $client;

    /**
     * Set up tests.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->entityManager = static::getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * Test invalid rating redirect.
     */
    public function testInvalidRatingRedirects(): void
    {
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $user = new User();
        $user->setUsername('user1');
        $user->setEmail('user@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, 'test'));
        $album = new Album();
        $category = new Category();

        $category->setTitle('Test category');
        $category->setCreatedAt(new \DateTimeImmutable());
        $album->setTitle('Test album');
        $album->setArtist('Test Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setCategory($category);
        $album->setAuthor($user);
        $this->entityManager->persist($user);
        $this->entityManager->persist($category);

        $this->entityManager->persist($album);

        $this->entityManager->flush();

        $this->client->loginUser($user);

        $this->client->request('POST', '/album/'.$album->getId().'/rate', [
            'rating' => 10,
        ]);

        $this->assertResponseRedirects('/'.$album->getId());
    }

    /**
     * Test valid rating redirect.
     */
    public function testValidRatingRedirects(): void
    {
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('user2@test.com');
        $user->setUsername('user2');
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, 'test'));
        $album = new Album();
        $category = new Category();
        $category->setTitle('Test category');
        $category->setCreatedAt(new \DateTimeImmutable());
        $album->setTitle('Test album');
        $album->setArtist('Test Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setCategory($category);
        $album->setAuthor($user);
        $this->entityManager->persist($user);
        $this->entityManager->persist($category);

        $this->entityManager->persist($album);
        $this->entityManager->flush();

        $this->client->loginUser($user);

        $this->client->request('POST', '/album/'.$album->getId().'/rate', [
            'rating' => 5,
        ]);

        $albumFromDb = $this->entityManager
            ->getRepository(Album::class)
            ->find($album->getId());

        $albumFromDb->getAuthor();

        $this->assertResponseRedirects('/'.$album->getId());
    }

    /**
     * Test guest user denied to rate.
     */
    public function testGuestUserIsDenied(): void
    {
        $user = new User();
        $user->setEmail('owner@test.com');
        $user->setUsername('owner');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('x');

        $this->entityManager->persist($user);

        $album = new Album();
        $category = new Category();
        $category->setTitle('Test category');
        $category->setCreatedAt(new \DateTimeImmutable());
        $album->setTitle('Test album');
        $album->setArtist('Test Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setCategory($category);
        $album->setAuthor($user);
        $this->entityManager->persist($category);

        $this->entityManager->persist($album);
        $this->entityManager->flush();

        $this->client->request('POST', '/album/'.$album->getId().'/rate', [
            'rating' => 5,
        ]);

        $this->assertResponseStatusCodeSame(302);
    }

    /**
     * Test denying blocked user to rate.
     */
    public function testBlockedUserCannotRate(): void
    {
        $user = new User();
        $user->setEmail('owner@test.com');
        $user->setUsername('owner');
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $user->setPassword('x');


        $album = new Album();
        $category = new Category();
        $category->setTitle('Test category');
        $category->setCreatedAt(new \DateTimeImmutable());
        $album->setTitle('Test album');
        $album->setArtist('Test Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setCategory($category);
        $album->setAuthor($user);
        $this->entityManager->persist($category);
        $this->entityManager->persist($album);

        $user->setIsBlocked(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);

        $this->client->request('POST', '/album/'.$album->getId().'/rate', [
            'rating' => 5,
        ]);

        $this->assertResponseRedirects('/'.$album->getId());

        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-danger');
    }
}
