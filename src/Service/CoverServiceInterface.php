<?php

/**
 * Cover service interface.
 */

namespace App\Service;

use App\Entity\Cover;
use App\Entity\Album;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Cover service.
 */
interface CoverServiceInterface
{
    /**
     * Create cover.
     *
     * @param UploadedFile $uploadedFile Uploaded file
     * @param Cover        $cover        Cover entity
     * @param Album        $album        Album entity
     */
    public function create(UploadedFile $uploadedFile, Cover $cover, Album $album): void;

    /**
     * Update avatar.
     *
     * @param UploadedFile $uploadedFile Uploaded file
     * @param Cover        $cover        Cover entity
     * @param Album        $album        Album interface
     */
    public function update(UploadedFile $uploadedFile, Cover $cover, Album $album): void;
}
