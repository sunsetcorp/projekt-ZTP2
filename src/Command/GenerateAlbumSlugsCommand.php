<?php

/**
 * Generate Album Slugs Command.
 */

namespace App\Command;

use App\Entity\Album;
use App\Repository\AlbumRepository;
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
     * @param AlbumRepository $albumRepository The album repository
     */
    public function __construct(private readonly AlbumRepository $albumRepository)
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
        $albums = $this->albumRepository->findAll();
        foreach ($albums as $album) {
            $album->setSlug((string) $album->getTitle());
        }

        $this->albumRepository->flush();
        $io->success('Slugs have been generated for all albums.');

        return Command::SUCCESS;
    }
}
