<?php

/**
 * Registration service tests.
 */

namespace App\Tests\Service;

use PHPUnit\Framework\MockObject\Exception;
use App\Entity\User;
use App\Form\Type\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use App\Service\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Class RegistrationServiceTest.
 */
class RegistrationServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private EntityManagerInterface $entityManager;
    /**
     * User repository.
     */
    private UserRepository $userRepository;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->userRepository = $container->get(UserRepository::class);
    }

    /**
     * Test successful registration.
     *
     * @throws Exception
     */
    public function testRegisterSuccess(): void
    {
        $user = new User();
        $user->setUsername('registration_test');
        $user->setEmail('registration@test.com');

        $request = new Request();

        $form = $this->createMock(FormInterface::class);
        $form->method('handleRequest');

        $form->method('isSubmitted')
            ->willReturn(true);

        $form->method('isValid')
            ->willReturn(true);

        $passwordField = $this->createMock(FormInterface::class);
        $passwordField->method('getData')
            ->willReturn('plainPassword');

        $form->method('get')
            ->with('password')
            ->willReturn($passwordField);

        $formFactory = $this->createMock(FormFactoryInterface::class);

        $formFactory->method('create')
            ->with(RegistrationFormType::class, $user)
            ->willReturn($form);

        $passwordHasher = static::getContainer()
            ->get(UserPasswordHasherInterface::class);

        $userAuthenticator = $this->createMock(
            UserAuthenticatorInterface::class
        );

        $userAuthenticator->expects($this->once())
            ->method('authenticateUser');

        $service = new RegistrationService(
            $formFactory,
            $passwordHasher,
            $userAuthenticator,
            $this->createMock(LoginFormAuthenticator::class),
            $this->createMock(Security::class),
            $this->userRepository
        );

        $result = $service->register($user, $request);

        $this->assertTrue($result);

        $savedUser = $this->userRepository->findOneBy([
            'email' => 'registration@test.com',
        ]);

        $this->assertNotNull($savedUser);
        $this->assertSame(
            'registration_test',
            $savedUser->getUsername()
        );

        $this->assertNotSame(
            'plainPassword',
            $savedUser->getPassword()
        );
    }

    /**
     * Test invalid form registration.
     *
     * @throws Exception
     */
    public function testRegisterReturnsFalseWhenFormInvalid(): void
    {
        $user = new User();

        $form = $this->createMock(FormInterface::class);

        $form->method('handleRequest');

        $form->method('isSubmitted')
            ->willReturn(true);

        $form->method('isValid')
            ->willReturn(false);

        $formFactory = $this->createMock(FormFactoryInterface::class);

        $formFactory->method('create')
            ->willReturn($form);

        $service = new RegistrationService(
            $formFactory,
            static::getContainer()->get(UserPasswordHasherInterface::class),
            $this->createMock(UserAuthenticatorInterface::class),
            $this->createMock(LoginFormAuthenticator::class),
            $this->createMock(Security::class),
            $this->userRepository
        );

        $result = $service->register($user, new Request());

        $this->assertFalse($result);
    }

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $user = $this->userRepository->findOneBy([
            'email' => 'registration@test.com',
        ]);

        if ($user) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }
    }

    /**
     * Test failing when duplicate user.
     *
     * @throws Exception
     */
    public function testRegisterFailsOnDuplicateUser(): void
    {
        $user = new User();
        $user->setUsername('duplicate');
        $user->setEmail('duplicate@test.com');

        $request = new Request();

        $form = $this->createMock(FormInterface::class);
        $form->method('handleRequest');

        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);

        $passwordField = $this->createMock(FormInterface::class);
        $passwordField->method('getData')->willReturn('plainPassword');

        $form->method('get')
            ->with('password')
            ->willReturn($passwordField);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->method('create')->willReturn($form);

        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $userAuthenticator = $this->createMock(UserAuthenticatorInterface::class);
        $userAuthenticator->expects($this->never())->method('authenticateUser');


        $userRepository = $this->createMock(UserRepository::class);

        $userRepository->method('save')
            ->willThrowException(new \RuntimeException('duplicate'));

        $service = new RegistrationService(
            $formFactory,
            $passwordHasher,
            $userAuthenticator,
            $this->createMock(LoginFormAuthenticator::class),
            $this->createMock(Security::class),
            $userRepository
        );

        $result = $service->register($user, $request);

        $this->assertFalse($result);
    }
}
