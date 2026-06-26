<?php

/**
 * Generate Album Slugs Command.
 */

namespace App\Command;

use App\Entity\Album;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Class GenerateAlbumSlugsCommand.
 *
 * Command to generate slugs for all albums.
 */
#[AsCommand(
    name: 'app:generate-album-slugs',
    description: 'Generate slugs for all albums'
)]
class GenerateAlbumSlugsCommand extends Command
{
    /**
     * Constructor for GenerateAlbumSlugsCommand.
     *
     * @param EntityManagerInterface $entityManager The entity manager
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
     * @return int Command exit status
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $albumRepository = $this->entityManager->getRepository(Album::class);
        $albums = $albumRepository->findAll();
        foreach ($albums as $album) {
            $album->setSlug((string) $album->getTitle());
            $this->entityManager->persist($album);
        }

        $this->entityManager->flush();
        $io->success('Slugs have been generated for all albums.');

        return Command::SUCCESS;
    }
}
