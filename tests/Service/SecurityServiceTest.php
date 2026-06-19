<?php

/**
 * Security service tests.
 */

namespace App\Tests\Service;

use PHPUnit\Framework\MockObject\Exception;
use App\Service\SecurityService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityServiceTest.
 */
class SecurityServiceTest extends TestCase
{
    /**
     * Test getting last authentication error returning message key.
     *
     * @throws Exception
     */
    public function testGetLastAuthenticationErrorReturnsMessageKey(): void
    {
        $exception = $this->createMock(AuthenticationException::class);
        $exception->method('getMessageKey')->willReturn('Invalid credentials');

        $authUtils = $this->createMock(AuthenticationUtils::class);
        $authUtils->method('getLastAuthenticationError')
            ->willReturn($exception);

        $service = new SecurityService($authUtils);

        $this->assertSame(
            'Invalid credentials',
            $service->getLastAuthenticationError()
        );
    }

    /**
     * Test getting last authentication error returning null.
     *
     * @throws Exception
     */
    public function testGetLastAuthenticationErrorReturnsNull(): void
    {
        $authUtils = $this->createMock(AuthenticationUtils::class);
        $authUtils->method('getLastAuthenticationError')
            ->willReturn(null);

        $service = new SecurityService($authUtils);

        $this->assertNull($service->getLastAuthenticationError());
    }

    /**
     * Test getting last username.
     *
     * @throws Exception
     */
    public function testGetLastUsername(): void
    {
        $authUtils = $this->createMock(AuthenticationUtils::class);
        $authUtils->method('getLastUsername')
            ->willReturn('john');

        $service = new SecurityService($authUtils);

        $this->assertSame('john', $service->getLastUsername());
    }
}
