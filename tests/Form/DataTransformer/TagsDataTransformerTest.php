<?php

/**
 * Tags data transformer tests.
 */

namespace App\Tests\Form\DataTransformer;

use PHPUnit\Framework\MockObject\Exception;
use App\Entity\Tag;
use App\Form\DataTransformer\TagsDataTransformer;
use App\Service\TagServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * Class TagsDataTransformerTest.
 */
class TagsDataTransformerTest extends TestCase
{
    /**
     * Test empty entry.
     *
     * @throws Exception
     */
    public function testTransformReturnsEmptyStringForEmptyCollection(): void
    {
        $tagService = $this->createMock(TagServiceInterface::class);

        $transformer = new TagsDataTransformer($tagService);

        $collection = new ArrayCollection();

        $this->assertSame('', $transformer->transform($collection));
    }

    /**
     * Test comma separated entries.
     *
     * @throws Exception
     */
    public function testTransformReturnsCommaSeparatedTitles(): void
    {
        $tag1 = new Tag();
        $tag1->setTitle('rock');

        $tag2 = new Tag();
        $tag2->setTitle('metal');

        $collection = new ArrayCollection([$tag1, $tag2]);

        $tagService = $this->createMock(TagServiceInterface::class);

        $transformer = new TagsDataTransformer($tagService);

        $result = $transformer->transform($collection);

        $this->assertSame('rock, metal', $result);
    }

    /**
     * Test case when tag already exists.
     *
     * @throws Exception
     */
    public function testReverseTransformReturnsExistingTag(): void
    {
        $tag = new Tag();
        $tag->setTitle('rock');

        $tagService = $this->createMock(TagServiceInterface::class);

        $tagService->expects($this->once())
            ->method('findOneByTitle')
            ->with('rock')
            ->willReturn($tag);

        $transformer = new TagsDataTransformer($tagService);

        $result = $transformer->reverseTransform('rock');

        $this->assertCount(1, $result);
        $this->assertSame($tag, $result[0]);
    }

    /**
     * Test empty array entry.
     *
     * @throws Exception
     */
    public function testReverseTransformReturnsEmptyArrayForEmptyString(): void
    {
        $tagService = $this->createMock(TagServiceInterface::class);

        $transformer = new TagsDataTransformer($tagService);

        $this->assertSame([], $transformer->reverseTransform(''));
    }

    /**
     * Test creating a new tag when missing.
     *
     * @throws Exception
     */
    public function testReverseTransformCreatesNewTagWhenMissing(): void
    {
        $tagService = $this->createMock(TagServiceInterface::class);

        $tagService->expects($this->once())
            ->method('findOneByTitle')
            ->willReturn(null);

        $tagService->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Tag::class));

        $transformer = new TagsDataTransformer($tagService);

        $result = $transformer->reverseTransform('rock');

        $this->assertCount(1, $result);

        $this->assertInstanceOf(Tag::class, $result[0]);
        $this->assertSame('rock', $result[0]->getTitle());
    }

    /**
     * Test ignoring empty values.
     *
     * @throws Exception
     */
    public function testReverseTransformIgnoresEmptyValues(): void
    {
        $tag = new Tag();
        $tag->setTitle('rock');

        $tagService = $this->createMock(TagServiceInterface::class);

        $tagService->expects($this->once())
            ->method('findOneByTitle')
            ->willReturn($tag);

        $transformer = new TagsDataTransformer($tagService);

        $result = $transformer->reverseTransform('rock,,,');

        $this->assertCount(1, $result);
    }
}
