<?php

/**
 * User role tests.
 */

namespace App\Tests\Entity\Enum;

use App\Entity\Enum\UserRole;
use PHPUnit\Framework\TestCase;

/**
 * Class UserRoleTest.
 */
class UserRoleTest extends TestCase
{
    /**
     * Test if values are correct.
     */
    public function testValuesAreCorrect(): void
    {
        $this->assertSame('ROLE_USER', UserRole::ROLE_USER->value);
        $this->assertSame('ROLE_ADMIN', UserRole::ROLE_ADMIN->value);
    }

    /**
     * Test if labels are correct.
     */
    public function testLabelsAreCorrect(): void
    {
        $this->assertSame('label.role_user', UserRole::ROLE_USER->label());
        $this->assertSame('label.role_admin', UserRole::ROLE_ADMIN->label());
    }

    /**
     * Test if cases are exhaustive.
     */
    public function testCasesAreExhaustive(): void
    {
        $this->assertCount(2, UserRole::cases());
    }
}
