<?php

/**
 * Cover service tests.
 */

namespace App\Tests\Service;

use PHPUnit\Framework\MockObject\Exception;
use App\Entity\Album;
use App\Entity\Cover;
use App\Repository\CoverRepository;
use App\Service\CoverService;
use App\Service\FileUploadServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class CoverServiceTest.
 */
class CoverServiceTest extends TestCase
{
    /**
     * Test creating and uploading cover.
     *
     * @throws Exception
     */
    public function testCreateUploadsAndSavesCover(): void
    {
        $coverRepository = $this->createMock(CoverRepository::class);
        $fileUploadService = $this->createMock(FileUploadServiceInterface::class);

        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cover = new Cover();
        $album = new Album();

        $fileUploadService->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn('cover.jpg');

        $coverRepository->expects($this->once())
            ->method('save')
            ->with($cover);

        $service = new CoverService($coverRepository, $fileUploadService);

        $service->create($uploadedFile, $cover, $album);

        $this->assertSame('cover.jpg', $cover->getFilename());
        $this->assertSame($album, $cover->getAlbum());
    }

    /**
     * Test deleting old file of cover and replacing it.
     *
     * @throws Exception
     */
    public function testUpdateDeletesOldFileAndCreatesNewOne(): void
    {
        $coverRepository = $this->createMock(CoverRepository::class);
        $fileUploadService = $this->createMock(FileUploadServiceInterface::class);

        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cover = new Cover();
        $cover->setFilename('old.jpg');

        $album = new Album();

        $fileUploadService->expects($this->once())
            ->method('delete')
            ->with('old.jpg');

        $fileUploadService->expects($this->once())
            ->method('upload')
            ->willReturn('new.jpg');

        $coverRepository->expects($this->once())
            ->method('save')
            ->with($cover);

        $service = new CoverService($coverRepository, $fileUploadService);

        $service->update($uploadedFile, $cover, $album);

        $this->assertSame('new.jpg', $cover->getFilename());
        $this->assertSame($album, $cover->getAlbum());
    }

    /**
     * Test updating without old file.
     *
     * @throws Exception
     */
    public function testUpdateWithoutOldFilenameDoesNotDelete(): void
    {
        $coverRepository = $this->createMock(CoverRepository::class);
        $fileUploadService = $this->createMock(FileUploadServiceInterface::class);

        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cover = new Cover();
        $album = new Album();

        $fileUploadService->expects($this->never())
            ->method('delete');

        $fileUploadService->expects($this->once())
            ->method('upload')
            ->willReturn('new.jpg');

        $coverRepository->expects($this->once())
            ->method('save');

        $service = new CoverService($coverRepository, $fileUploadService);

        $service->update($uploadedFile, $cover, $album);

        $this->assertSame('new.jpg', $cover->getFilename());
        $this->assertSame($album, $cover->getAlbum());
    }
}
