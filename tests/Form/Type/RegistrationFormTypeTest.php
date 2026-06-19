<?php

/**
 * Registration form type tests.
 */

namespace App\Tests\Form\Type;

use App\Entity\User;
use App\Form\Type\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

/**
 * Class RegistrationFormTypeTest.
 */
class RegistrationFormTypeTest extends KernelTestCase
{
    private FormFactoryInterface $factory;

    /**
     * Set up tests.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $validator = Validation::createValidator();

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtension(new CoreExtension())
            ->addExtension(new ValidatorExtension($validator))
            ->getFormFactory();
    }

    /**
     * Test when submit data is valid.
     */
    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => 'test@test.com',
            'username' => 'testuser',
            'password' => [
                'first' => 'password123',
                'second' => 'password123',
            ],
        ];

        $model = new User();

        $form = $this->factory->create(RegistrationFormType::class, $model);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $this->assertSame('test@test.com', $model->getEmail());
        $this->assertSame('testuser', $model->getUsername());
    }

    /**
     * Test when there is a password mismatch.
     */
    public function testPasswordMismatchIsInvalid(): void
    {
        $formData = [
            'email' => 'test@test.com',
            'username' => 'testuser',
            'password' => [
                'first' => 'password123',
                'second' => 'different',
            ],
        ];

        $form = $this->factory->create(RegistrationFormType::class);

        $form->submit($formData);

        $this->assertFalse($form->isValid());
    }

    /**
     * Test if email is blank.
     */
    public function testBlankEmailIsInvalid(): void
    {
        $formData = [
            'email' => '',
            'username' => 'testuser',
            'password' => [
                'first' => 'password123',
                'second' => 'password123',
            ],
        ];

        $form = $this->factory->create(RegistrationFormType::class);

        $form->submit($formData);

        $this->assertFalse($form->isValid());
    }
}
