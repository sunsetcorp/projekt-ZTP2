<?php

/**
 * Cover service.
 */

namespace App\Service;

use App\Entity\Cover;
use App\Entity\Album;
use App\Repository\CoverRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Cover service.
 */
class CoverService implements CoverServiceInterface
{
    /**
     * Constructor.
     *
     * @param CoverRepository            $coverRepository   Cover repository
     * @param FileUploadServiceInterface $fileUploadService File upload service
     */
    public function __construct(private readonly CoverRepository $coverRepository, private readonly FileUploadServiceInterface $fileUploadService)
    {
    }

    /**
     * Create cover.
     *
     * @param UploadedFile   $uploadedFile Uploaded file
     * @param Cover          $cover        Cover entity
     * @param AlbumInterface $album        Album interface
     */
    public function create(UploadedFile $uploadedFile, Cover $cover, Album $album): void
    {
        $coverFilename = $this->fileUploadService->upload($uploadedFile);

        $cover->setAlbum($album);
        $cover->setFilename($coverFilename);
        $this->coverRepository->save($cover);
    }

    /**
     * Update avatar.
     *
     * @param UploadedFile $uploadedFile Uploaded file
     * @param Cover        $cover        Cover entity
     * @param Album        $album        Album Entity
     */
    public function update(UploadedFile $uploadedFile, Cover $cover, Album $album): void
    {
        $filename = $cover->getFilename();

        if ($filename) {
            $this->fileUploadService->delete($filename);
        }

        $this->create($uploadedFile, $cover, $album);
    }

    /**
     * Find cover by album.
     *
     * @param Album $album The album
     *
     * @return Cover|null Find cover for album
     */
    public function findByAlbum(Album $album): ?Cover
    {
        return $this->coverRepository->findOneByAlbum($album);
    }
}
