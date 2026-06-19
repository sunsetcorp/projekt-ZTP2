<?php

/**
 * Generate Category Slugs Command.
 */

namespace App\Command;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Class Generate Category Slugs Command.
 */
#[AsCommand(
    name: 'app:generate-category-slugs',
    description: 'Generate slugs for all categories'
)]
class GenerateCategorySlugsCommand extends Command
{
    /**
     * GenerateCategorySlugsCommand constructor.
     *
     * @param EntityManagerInterface $entityManager The entity manager to interact with the database
     */
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  The input interface
     * @param OutputInterface $output The output interface
     *
     * @return int The command exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        foreach ($categories as $category) {
            $category->setSlug((string) $category->getTitle()); // Temporary assignment to force slug generation
            $this->entityManager->persist($category);
        }

        $this->entityManager->flush();

        $io->success('Slugs have been generated for all categories.');

        return Command::SUCCESS;
    }
}
