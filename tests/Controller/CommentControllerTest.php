<?php

/**
 * Comment controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Comment;
use App\Entity\Category;
use App\Entity\User;
use App\Entity\Album;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class CommentControllerTest.
 */
class CommentControllerTest extends WebTestCase
{
    /**
     * Test adding a comment as an authenticated user.
     */
    public function testAddCommentAsAuthenticatedUser(): void
    {
        $client = static::createClient();
        $em = self::getContainer()->get('doctrine')->getManager();
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setUsername('user555541');
        $user->setEmail('user@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, 'test'));
        $em->persist($user);
        $category = new Category();
        $category->setTitle('Test category');
        $category->setCreatedAt(new \DateTimeImmutable());

        $em->persist($category);
        $album = new Album();
        $album->setTitle('Test album');
        $album->setArtist('Test Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setCategory($category);
        $album->setAuthor($user);
        $em->persist($album);

        $em->flush();

        $client->loginUser($user);

        $crawler = $client->request('GET', '/'.$album->getId());

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('[data-testid="add-comment"]')->form([
            'comment[content]' => 'Test comment',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/'.$album->getId());
    }

    /**
     * Test deleting a comment as an admin.
     */
    public function testDeleteCommentAsAdmin(): void
    {
        $client = static::createClient();
        $em = self::getContainer()->get('doctrine')->getManager();
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($hasher->hashPassword($admin, 'admin'));
        $category = new Category();
        $category->setTitle('Test category');
        $category->setCreatedAt(new \DateTimeImmutable());

        $em->persist($category);
        $album = new Album();
        $album->setTitle('Test album');
        $album->setArtist('Test Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setCategory($category);
        $album->setAuthor($admin);
        $em->persist($album);

        $comment = new Comment();
        $comment->setContent('Test comment');
        $comment->setAlbum($album);
        $comment->setAuthor($admin);
        $comment->setCreatedAt(new \DateTimeImmutable());

        $em->persist($admin);
        $em->persist($album);
        $em->persist($comment);
        $em->flush();

        $client->loginUser($admin);

        $client->request('POST', '/comment/'.$comment->getId().'/delete', [
            'comment_delete' => [],
        ]);

        $this->assertResponseRedirects();
    }
}
