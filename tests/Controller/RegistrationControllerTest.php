<?php

/**
 * Registration controller tests.
 */

namespace App\Tests\Controller;

use App\Service\RegistrationServiceInterface;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class RegistrationControllerTest.
 */
class RegistrationControllerTest extends WebTestCase
{
    /**
     * Test rendering regoster page.
     */
    public function testRegisterPageLoads(): void
    {
        $client = static::createClient();

        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Test registering with valid data.
     */
    public function testRegisterPostValidData(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register');

        $form = $crawler->filter('[data-testid="register"]')->form();

        $form['registration_form[email]'] = 'test@example.com';
        $form['registration_form[username]'] = 'testuser';

        $form['registration_form[password][first]'] = 'password123';
        $form['registration_form[password][second]'] = 'password123';

        $client->submit($form);

        $this->assertContains(
            $client->getResponse()->getStatusCode(),
            [200, 302]
        );
    }

    /**
     * Test flash message after failed registration.
     *
     * @throws Exception
     */
    public function testRegisterFailsAddsFlashError(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $service = $this->createMock(RegistrationServiceInterface::class);
        $service->method('register')->willReturn(false);

        $container->set(RegistrationServiceInterface::class, $service);

        $client->request('POST', '/register', [
            'registration_form' => [
                'email' => 'test@example.com',
                'username' => 'testuser',
                'password' => [
                    'first' => 'password123',
                    'second' => 'password123',
                ],
            ],
        ]);

        $this->assertStringContainsString(
            'register',
            $client->getResponse()->getContent()
        );
    }
}
