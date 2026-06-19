<?php

/**
 * Generate category slugs command tests.
 */

namespace App\Tests\Command;

use Doctrine\ORM\Exception\ORMException;
use App\Command\GenerateCategorySlugsCommand;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class GenerateCategorySlugsCommandTest.
 */
class GenerateCategorySlugsCommandTest extends KernelTestCase
{
    /**
     * Test of slug execution.
     *
     * @throws ORMException
     */
    public function testExecute(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $category = new Category();
        $category->setTitle('Rock Music');
        $category->setCreatedAt(new \DateTimeImmutable());

        $em->persist($category);
        $em->flush();

        $command = $container->get(GenerateCategorySlugsCommand::class);

        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());

        $this->assertStringContainsString(
            'Slugs have been generated for all categories.',
            $commandTester->getDisplay()
        );

        $em->refresh($category);

        $this->assertNotNull($category->getSlug());
    }
}
