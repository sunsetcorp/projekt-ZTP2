<?php

/**
 * Security controller tests.
 */

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SecurityControllerTests.
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * Test consturctor.
     */
    public function testConstructor(): void
    {
        self::bootKernel();

        $controller = self::getContainer()->get(
            SecurityController::class
        );

        $this->assertInstanceOf(
            SecurityController::class,
            $controller
        );
    }

    /**
     * Test loading login page.
     */
    public function testLoginPageLoads(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Test logging out.
     */
    public function testLogoutThrowsException(): void
    {
        $controller = self::getContainer()->get(
            SecurityController::class
        );

        $this->expectException(\LogicException::class);

        $controller->logout();
    }

    /**
     * Test if logout route exists.
     */
    public function testLogoutRouteExists(): void
    {
        $this->expectException(\LogicException::class);

        self::bootKernel();

        $controller = self::getContainer()->get(
            SecurityController::class
        );

        $controller->logout();
    }
}
