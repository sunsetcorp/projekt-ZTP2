<?php

/**
 * Account service tests.
 */

namespace App\Tests\Service;

use PHPUnit\Framework\MockObject\Exception;
use App\Entity\User;
use App\Form\Type\AccountType;
use App\Repository\AdminRepository;
use App\Repository\UserRepository;
use App\Service\AccountService;
use App\Service\AccountServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Symfony\Component\Form\FormView;

/**
 * Class AccountServiceTest.
 */
class AccountServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Account service.
     */
    private ?AccountServiceInterface $accountService;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->entityManager = $container->get('doctrine.orm.entity_manager');

        $this->accountService = new AccountService(
            $this->entityManager,
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(Security::class),
            $this->createMock(FormFactoryInterface::class),
            $this->createMock(Environment::class),
            $this->createMock(UserRepository::class),
            $this->createMock(AdminRepository::class),
            $this->createMock(TranslatorInterface::class)
        );
    }

    /**
     * Test get current user.
     */
    public function testGetCurrentUser(): void
    {
        $expectedUser = new User();
        $expectedUser->setEmail('test@example.com');

        $security = $this->createMock(Security::class);
        $security->method('getUser')
            ->willReturn($expectedUser);

        $service = new AccountService(
            $this->entityManager,
            $this->createMock(UserPasswordHasherInterface::class),
            $security,
            $this->createMock(FormFactoryInterface::class),
            $this->createMock(Environment::class),
            $this->createMock(UserRepository::class),
            $this->createMock(AdminRepository::class),
            $this->createMock(TranslatorInterface::class)
        );

        $result = $service->getCurrentUser();

        $this->assertSame($expectedUser, $result);
    }

    /**
     * Test render account page.
     */
    public function testRenderAccountPage(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $security = $this->createMock(Security::class);
        $security->method('getUser')
            ->willReturn($user);

        $twig = $this->createMock(Environment::class);

        $twig->expects($this->once())
            ->method('render')
            ->with(
                'account/account.html.twig',
                ['user' => $user]
            )
            ->willReturn('account page');

        $service = new AccountService(
            $this->entityManager,
            $this->createMock(UserPasswordHasherInterface::class),
            $security,
            $this->createMock(FormFactoryInterface::class),
            $twig,
            $this->createMock(UserRepository::class),
            $this->createMock(AdminRepository::class),
            $this->createMock(TranslatorInterface::class)
        );

        $result = $service->renderAccountPage();

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame('account page', $result->getContent());
    }

    /**
     * Test access denied when user not logged in.
     */
    public function testHandleAccountEditReturnsAccessDenied(): void
    {
        $security = $this->createMock(Security::class);

        $security->method('getUser')
            ->willReturn(null);

        $twig = $this->createMock(Environment::class);

        $twig->expects($this->once())
            ->method('render')
            ->with('error/accessdenied.html.twig')
            ->willReturn('access denied');

        $service = new AccountService(
            $this->entityManager,
            $this->createMock(UserPasswordHasherInterface::class),
            $security,
            $this->createMock(FormFactoryInterface::class),
            $twig,
            $this->createMock(UserRepository::class),
            $this->createMock(AdminRepository::class),
            $this->createMock(TranslatorInterface::class)
        );

        $result = $service->handleAccountEdit(new Request());

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame('access denied', $result->getContent());
    }

    /**
     * Test successful account edit.
     */
    public function testHandleAccountEditSuccess(): void
    {
        $user = $this->createMock(User::class);

        $user->method('getRoles')
            ->willReturn(['ROLE_USER']);

        $security = $this->createMock(Security::class);

        $security->method('getUser')
            ->willReturn($user);

        $passwordField = $this->createMock(FormInterface::class);

        $passwordField->method('getData')
            ->willReturn('new-password');

        $form = $this->createMock(FormInterface::class);

        $form->method('isSubmitted')
            ->willReturn(true);

        $form->method('isValid')
            ->willReturn(true);

        $form->method('has')
            ->with('roles')
            ->willReturn(false);

        $form->method('get')
            ->with('plainPassword')
            ->willReturn($passwordField);

        $formFactory = $this->createMock(FormFactoryInterface::class);

        $formFactory->expects($this->once())
            ->method('create')
            ->with(
                AccountType::class,
                $user,
                ['is_admin' => false]
            )
            ->willReturn($form);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'new-password')
            ->willReturn('hashed-password');

        $user->expects($this->once())
            ->method('setPassword')
            ->with('hashed-password');

        $userRepository = $this->createMock(UserRepository::class);

        $userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        $service = new AccountService(
            $this->entityManager,
            $passwordHasher,
            $security,
            $formFactory,
            $this->createMock(Environment::class),
            $userRepository,
            $this->createMock(AdminRepository::class),
            $this->createMock(TranslatorInterface::class)
        );

        $result = $service->handleAccountEdit(new Request());

        $this->assertIsArray($result);
        $this->assertSame('success', $result['status']);
    }

    /**
     * Test last admin cannot lose admin role.
     */
    public function testLastAdminCannotLoseAdminRole(): void
    {
        $user = $this->createMock(User::class);

        $user->method('getRoles')
            ->willReturn(['ROLE_ADMIN']);

        $security = $this->createMock(Security::class);

        $security->method('getUser')
            ->willReturn($user);

        $rolesField = $this->createMock(FormInterface::class);

        $rolesField->method('getData')
            ->willReturn([]);

        $plainPasswordField = $this->createMock(FormInterface::class);

        $plainPasswordField->method('getData')
            ->willReturn(null);

        $form = $this->createMock(FormInterface::class);

        $form->method('isSubmitted')
            ->willReturn(true);

        $form->method('isValid')
            ->willReturn(true);

        $form->method('has')
            ->with('roles')
            ->willReturn(true);

        $form->method('get')
            ->willReturnMap([
                ['roles', $rolesField],
                ['plainPassword', $plainPasswordField],
            ]);

        $formFactory = $this->createMock(FormFactoryInterface::class);

        $formFactory->method('create')
            ->willReturn($form);

        $adminRepository = $this->createMock(AdminRepository::class);

        $adminRepository->expects($this->once())
            ->method('countOtherAdmins')
            ->with($user)
            ->willReturn(0);

        $service = new AccountService(
            $this->entityManager,
            $this->createMock(UserPasswordHasherInterface::class),
            $security,
            $formFactory,
            $this->createMock(Environment::class),
            $this->createMock(UserRepository::class),
            $adminRepository,
            $this->createMock(TranslatorInterface::class)
        );

        $result = $service->handleAccountEdit(new Request());

        $this->assertIsArray($result);
        $this->assertSame('admin_error', $result['status']);
    }

    /**
     * Test handling edit when form is not submitted.
     *
     * @throws Exception
     */
    public function testHandleAccountEditRendersFormWhenNotSubmitted(): void
    {
        $user = $this->createMock(User::class);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $form = $this->createMock(FormInterface::class);

        $form->method('isSubmitted')->willReturn(false);
        $form->method('isValid')->willReturn(false);

        $form->method('createView')
            ->willReturn(new FormView());

        $formFactory = $this->createMock(FormFactoryInterface::class);

        $formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form);

        $twig = $this->createMock(Environment::class);

        $twig->expects($this->once())
            ->method('render')
            ->with(
                'account/edit.html.twig',
                $this->callback(fn ($data) => isset($data['accountForm'])
                && $data['accountForm'] instanceof FormView)
            )
            ->willReturn('edit-page-html');

        $service = new AccountService(
            $this->entityManager,
            $this->createMock(UserPasswordHasherInterface::class),
            $security,
            $formFactory,
            $twig,
            $this->createMock(UserRepository::class),
            $this->createMock(AdminRepository::class),
            $this->createMock(TranslatorInterface::class)
        );

        $response = $service->handleAccountEdit(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('edit-page-html', $response->getContent());
    }
}
