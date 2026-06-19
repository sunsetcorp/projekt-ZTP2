<?php

/**
 * Generate album slugs command tests.
 */

namespace App\Tests\Command;

use App\Command\GenerateAlbumSlugsCommand;
use App\Entity\Album;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class GenerateAlbumSlugsCommandTest.
 */
class GenerateAlbumSlugsCommandTest extends KernelTestCase
{
    /**
     * Test of slug execution.
     */
    public function testExecute(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $category = new Category();
        $category->setTitle('Test Category');
        $category->setCreatedAt(new \DateTimeImmutable());

        $user = new User();
        $user->setUsername('author');
        $user->setEmail('author@test.com');
        $user->setPassword('test'); // or hashed if required

        $em->persist($category);
        $em->persist($user);

        $album = new Album();
        $album->setTitle('Test Album');
        $album->setArtist('Test Artist');
        $album->setReleaseDate(new \DateTime());
        $album->setCategory($category);
        $album->setAuthor($user);

        $em->persist($album);
        $em->flush();

        $application = new Application();

        $command = $container->get(GenerateAlbumSlugsCommand::class);

        $application->addCommand($command);

        $commandTester = new CommandTester(
            $application->find('app:generate-album-slugs')
        );

        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString(
            'Slugs have been generated for all albums.',
            $output
        );

        $em->refresh($album);

        $this->assertNotNull($album->getSlug());
    }
}
