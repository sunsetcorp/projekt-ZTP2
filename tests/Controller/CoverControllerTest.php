<?php

/**
 * Cover Controller Tests.
 */

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Album;
use App\Entity\Cover;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class CoverControllerTests.
 */
class CoverControllerTest extends WebTestCase
{
    /**
     * Test creating covers.
     */
    public function testCreateCover(): void
    {
        $client = static::createClient();
        $em = self::getContainer()->get('doctrine')->getManager();
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $admin = new User();
        $admin->setUsername('admin_'.uniqid());
        $admin->setEmail('admin_'.uniqid().'@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($hasher->hashPassword($admin, 'admin'));

        $album = new Album();
        $album->setTitle('Test Album');
        $album->setArtist('Test Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setAuthor($admin);

        $em->persist($admin);
        $em->persist($album);
        $em->flush();

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/cover/cover/create/'.$album->getId());

        $this->assertResponseIsSuccessful();

        $filePath = sys_get_temp_dir().'/test.jpg';
        file_put_contents($filePath, base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAA=='));

        $form = $crawler->filter('[data-testid="submit"]')->form();

        $form['cover[file]']->upload($filePath);

        $client->submit($form);

        $this->assertResponseRedirects();
    }

    /**
     * Test redirecting to edit if cover exists.
     */
    public function testCreateRedirectsIfCoverExists(): void
    {
        $client = static::createClient();
        $em = self::getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setUsername('u1');
        $user->setEmail('u1@test.com');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword('x');

        $album = new Album();
        $album->setTitle('A');
        $album->setArtist('B');
        $album->setReleaseDate(new \DateTime());
        $album->setAuthor($user);

        $cover = new Cover();
        $cover->setFileName('test.jpg');
        $cover->setAlbum($album);
        $album->setCover($cover);
        $em->persist($user);
        $em->persist($album);
        $em->persist($cover);
        $em->flush();

        $client->loginUser($user);

        $client->request('GET', '/cover/cover/create/'.$album->getId());

        $this->assertResponseRedirects();
    }

    /**
     * Test redirecting if cover does not exist.
     */
    public function testEditRedirectsIfNoCover(): void
    {
        $client = static::createClient();
        $em = self::getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setUsername('u2');
        $user->setEmail('u2@test.com');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword('x');

        $album = new Album();
        $album->setTitle('A');
        $album->setArtist('B');
        $album->setReleaseDate(new \DateTime());
        $album->setAuthor($user);

        $cover = new Cover();
        $cover->setFileName('test.jpg');
        $cover->setAlbum($album);

        $em->persist($user);
        $em->persist($album);
        $em->persist($cover);
        $em->flush();

        $client->loginUser($user);

        $album->setCover(null);
        $em->flush();

        $client->request('GET', '/cover/'.$cover->getId().'/edit');

        $this->assertResponseRedirects();
    }
}
