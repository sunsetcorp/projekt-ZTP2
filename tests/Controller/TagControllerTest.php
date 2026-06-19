<?php

/**
 * Tag controller tests.
 */

namespace App\Tests\Controller;

use PHPUnit\Framework\MockObject\Exception;
use App\Entity\Tag;
use App\Service\TagServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Class TagControllerTests.
 */
class TagControllerTest extends WebTestCase
{
    /**
     * Test loading index page.
     *
     * @throws Exception
     */
    public function testIndexPageLoads(): void
    {
        $client = static::createClient();
        $pagination = $this->createMock(PaginationInterface::class);

        $tagService = $this->createMock(TagServiceInterface::class);
        $tagService->method('getPaginatedList')->willReturn($pagination);

        self::getContainer()->set(TagServiceInterface::class, $tagService);

        $client->request('GET', '/tag');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test of the tag show page.
     */
    public function testShowPageLoads(): void
    {
        $client = static::createClient();

        $em = self::getContainer()->get('doctrine')->getManager();

        $tag = new Tag();
        $tag->setTitle('Rock');

        $em->persist($tag);
        $em->flush();

        $client->request('GET', '/tag/'.$tag->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test creating a tag.
     *
     * @throws Exception
     */
    public function testCreateTag(): void
    {
        $client = static::createClient();

        $tagService = $this->createMock(TagServiceInterface::class);
        $tagService->method('save');
        self::getContainer()->set(TagServiceInterface::class, $tagService);

        $crawler = $client->request('GET', '/tag/create');

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('[data-testid="submit"]')->form();

        $form['tag[title]'] = 'Jazz';

        $client->submit($form);
        $this->assertResponseRedirects('/tag');
    }

    /**
     * Test editing a tag.
     */
    public function testEditTag(): void
    {
        $client = static::createClient();

        $em = self::getContainer()->get('doctrine')->getManager();

        $tag = new Tag();
        $tag->setTitle('Old');

        $em->persist($tag);
        $em->flush();

        $crawler = $client->request('GET', '/tag/'.$tag->getId().'/edit');

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();
        $form['tag[title]'] = 'Updated';

        $client->submit($form);

        $this->assertResponseRedirects('/tag');

        $em->clear();

        $updatedTag = $em->getRepository(Tag::class)->find($tag->getId());

        $this->assertSame('Updated', $updatedTag->getTitle());
    }

    /**
     * Test deleting a blocked tag.
     *
     * @throws Exception
     */
    public function testDeleteBlockedTag(): void
    {
        $client = static::createClient();

        $em = self::getContainer()->get('doctrine')->getManager();

        $tag = new Tag();
        $tag->setTitle('Blocked');

        $em->persist($tag);
        $em->flush();

        $tagService = $this->createMock(TagServiceInterface::class);
        $tagService->method('canBeDeleted')->willReturn(false);

        self::getContainer()->set(TagServiceInterface::class, $tagService);

        $client->request('GET', '/tag/'.$tag->getId().'/delete');

        $this->assertResponseRedirects('/tag');
    }
}
