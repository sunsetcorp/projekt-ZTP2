<?php

/**
 * File upload service interface.
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface FileUploadService.
 */
interface FileUploadServiceInterface
{
    /**
     * Upload file.
     *
     * @param UploadedFile $file File to upload
     *
     * @return string Filename of uploaded file
     */
    public function upload(UploadedFile $file): string;

    /**
     * Test deleting file.
     *
     * @param string $filename Filename of the deleted file
     */
    public function delete(string $filename): void;

    /**
     * Getter for target directory.
     *
     * @return string Target directory
     */
    public function getTargetDirectory(): string;
}
