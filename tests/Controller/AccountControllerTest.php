<?php

/**
 *Account controller tests.
 */

namespace App\Tests\Controller;

use PHPUnit\Framework\MockObject\Exception;
use App\Service\AccountServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AccountControllerTest.
 */
class AccountControllerTest extends WebTestCase
{
    /**
     * Test for rendering account page.
     *
     * @throws Exception
     */
    public function testAccountPageRenders(): void
    {
        $client = static::createClient();

        $mock = $this->createMock(AccountServiceInterface::class);
        $mock->method('renderAccountPage')
            ->willReturn(new Response('ACCOUNT PAGE'));

        static::getContainer()->set(AccountServiceInterface::class, $mock);

        $client->request('GET', '/account');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('ACCOUNT PAGE', $client->getResponse()->getContent());
    }

    /**
     *Test for redirecting after editing.
     *
     * @throws Exception
     */
    public function testEditSuccessRedirectsToAccount(): void
    {
        $client = static::createClient();

        $container = static::getContainer();

        $service = $this->createMock(AccountServiceInterface::class);
        $translator = $container->get(TranslatorInterface::class);

        $service->method('handleAccountEdit')
            ->willReturn(['status' => 'success']);

        $container->set(AccountServiceInterface::class, $service);

        $client->request('POST', '/account/edit');

        $this->assertResponseRedirects('/account');
    }

    /**
     * Test for failed admin edit.
     *
     * @throws Exception
     */
    public function testEditAdminErrorRedirectsBack(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $service = $this->createMock(AccountServiceInterface::class);

        $service->method('handleAccountEdit')
            ->willReturn(['status' => 'admin_error']);

        $container->set(AccountServiceInterface::class, $service);

        $client->request('POST', '/account/edit');

        $this->assertResponseRedirects('/account/edit');
    }

    /**
     * Test for flashing message after succeful edit.
     *
     * @throws Exception
     */
    public function testEditSuccessSetsFlashMessage(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $service = $this->createMock(AccountServiceInterface::class);

        $service->method('handleAccountEdit')
            ->willReturn(['status' => 'success']);

        $container->set(AccountServiceInterface::class, $service);

        $client->request('POST', '/account/edit');

        $session = $client->getRequest()->getSession();

        $flashes = $session->getFlashBag()->get('success');

        $this->assertNotEmpty($flashes);
    }

    /**
     * Tests that the edit action displays the response returned by the account service.
     *
     * @throws Exception
     */
    public function testEditReturnsResponseWhenServiceReturnsResponse(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $service = $this->createMock(AccountServiceInterface::class);

        $service->method('handleAccountEdit')
            ->willReturn(new Response('EDIT FORM HTML'));

        $container->set(AccountServiceInterface::class, $service);

        $client->request('POST', '/account/edit');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('EDIT FORM HTML', $client->getResponse()->getContent());
    }
}
