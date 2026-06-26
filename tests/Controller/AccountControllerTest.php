<?php

/**
 *Account controller tests.
 */

namespace App\Tests\Controller;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use PHPUnit\Framework\MockObject\Exception;
use App\Service\AccountServiceInterface;
use App\Security\Voter\UserBlockedVoter;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
            ->willReturn([
                'status' => 'access_denied',
            ]);

        $container->set(AccountServiceInterface::class, $service);

        $client->request('POST', '/account/edit');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test returning invalid response form edit.
     *
     * @throws Exception
     */
    public function testEditReturnsFormInvalidResponse(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $formMock = $this->createMock(FormInterface::class);
        $formMock->method('createView')
            ->willReturn(new FormView());

        $service = $this->createMock(AccountServiceInterface::class);

        $service->method('handleAccountEdit')
            ->willReturn([
                'status' => 'form_invalid',
                'form' => $formMock,
            ]);

        $container->set(AccountServiceInterface::class, $service);

        $client->request('POST', '/account/edit');

        $this->assertResponseIsSuccessful();
    }

    /**
     *  Test that voter denies access when token user is not a valid User instance.
     *
     * @throws Exception
     */
    public function testNonUserDenied(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        $voter = new UserBlockedVoter();

        $ref = new \ReflectionMethod($voter, 'voteOnAttribute');

        $result = $ref->invoke(
            $voter,
            UserBlockedVoter::NOT_BLOCKED,
            null,
            $token
        );

        $this->assertFalse($result);
    }
}
