<?php

/**
 * Account type tests.
 */

namespace App\Tests\Form\Type;

use App\Entity\User;
use App\Form\Type\AccountType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class AccountTypeTest.
 */
class AccountTypeTest extends KernelTestCase
{
    private FormFactoryInterface $factory;

    /**
     * Set up tests.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->factory = self::getContainer()->get('form.factory');
    }

    /**
     * Test when passwords mismatch.
     */
    public function testPasswordMismatchIsInvalid(): void
    {
        $user = new User();

        $form = $this->factory->create(AccountType::class, $user);

        $form->submit([
            'username' => 'testuser',
            'email' => 'test@test.com',
            'plainPassword' => 'secret123',
            'repeatPassword' => 'wrong',
        ]);

        $this->assertFalse($form->isValid());
    }

    /**
     * Test admin roles.
     */
    public function testAdminGetsRolesField(): void
    {
        $form = $this->factory->create(AccountType::class, null, [
            'is_admin' => true,
        ]);

        $this->assertTrue($form->has('roles'));
    }
}
