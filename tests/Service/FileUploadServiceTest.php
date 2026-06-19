<?php

/**
 * File upload service tests.
 */

namespace App\Tests\Service;

use PHPUnit\Framework\MockObject\Exception;
use App\Service\FileUploadService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class FileUploadServiceTest.
 */
class FileUploadServiceTest extends TestCase
{
    /**
     * Test uploading and generating filename.
     *
     * @throws Exception
     */
    public function testUploadReturnsGeneratedFilename(): void
    {
        $slugger = $this->createMock(SluggerInterface::class);

        $uploadedFile = $this->createMock(UploadedFile::class);

        $uploadedFile->method('guessExtension')
            ->willReturn('jpg');

        $uploadedFile->method('getClientOriginalName')
            ->willReturn('My Cover.jpg');

        $slugger->method('slug')
            ->with('My Cover')
            ->willReturn(new UnicodeString('my-cover'));

        $uploadedFile->expects($this->once())
            ->method('move');

        $service = new FileUploadService(
            'uploads',
            $slugger
        );

        $result = $service->upload($uploadedFile);

        $this->assertStringStartsWith('my-cover-', $result);

        $this->assertStringEndsWith('.jpg', $result);
    }

    /**
     * Test uploading and returning empty string when extension is null.
     *
     * @throws Exception
     */
    public function testUploadReturnsEmptyStringWhenExtensionIsNull(): void
    {
        $slugger = $this->createMock(SluggerInterface::class);

        $uploadedFile = $this->createMock(UploadedFile::class);

        $uploadedFile->method('guessExtension')
            ->willReturn(null);

        $uploadedFile->expects($this->once())
            ->method('move');

        $service = new FileUploadService(
            'uploads',
            $slugger
        );

        $result = $service->upload($uploadedFile);

        $this->assertSame('', $result);
    }

    /**
     * Test target if directory returns correct path.
     *
     * @throws Exception
     */
    public function testGetTargetDirectoryReturnsCorrectPath(): void
    {
        $slugger = $this->createMock(SluggerInterface::class);

        $service = new FileUploadService(
            'uploads',
            $slugger
        );

        $this->assertSame(
            'uploads',
            $service->getTargetDirectory()
        );
    }

    /**
     * Test handling exceptions when uploading.
     *
     * @throws Exception
     */
    public function testUploadHandlesFileException(): void
    {
        $slugger = $this->createMock(SluggerInterface::class);

        $uploadedFile = $this->createMock(UploadedFile::class);

        $uploadedFile->method('guessExtension')
            ->willReturn('jpg');

        $uploadedFile->method('getClientOriginalName')
            ->willReturn('test.jpg');

        $slugger->method('slug')
            ->willReturn(new UnicodeString('test'));

        $uploadedFile->method('move')
            ->willThrowException(new FileException());

        $service = new FileUploadService(
            'uploads',
            $slugger
        );

        $result = $service->upload($uploadedFile);

        $this->assertStringStartsWith('test-', $result);
    }

    /**
     * Test removing the file.
     *
     * @throws Exception
     */
    public function testDeleteRemovesFile(): void
    {
        $filesystem = new Filesystem();
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'data');

        $service = new FileUploadService(
            sys_get_temp_dir(),
            $this->createMock(SluggerInterface::class)
        );

        $filename = basename($tempFile);

        $service->delete($filename);

        $this->assertFileDoesNotExist($tempFile);
    }
}
