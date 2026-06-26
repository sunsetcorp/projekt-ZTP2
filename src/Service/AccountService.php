<?php

/**
 * Account service.
 */

namespace App\Service;

/*
 * Service for handling user account operations.
 */

use App\Entity\User;
use App\Form\Type\AccountType;
use App\Repository\UserRepository;
use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class Account Service.
 */
class AccountService implements AccountServiceInterface
{
    /**
     * Constructor.
     *
     * @param EntityManagerInterface      $entityManager   The entity manager interface
     * @param UserPasswordHasherInterface $passwordHasher  The password hasher interface
     * @param Security                    $security        The security component for accessing the current user
     * @param FormFactoryInterface        $formFactory     The form factory interface
     * @param Environment                 $twig            The Twig environment for rendering templates
     * @param User                        $userRepository  User Repository
     * @param Admin                       $adminRepository Admin Repository
     * @param TranslatorInterface         $translator      The translator
     */
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UserPasswordHasherInterface $passwordHasher, private readonly Security $security, private readonly FormFactoryInterface $formFactory, private readonly Environment $twig, private readonly UserRepository $userRepository, private readonly AdminRepository $adminRepository, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Retrieves the current authenticated user.
     *
     * @return mixed|null The current user object or null if no user is authenticated
     */
    public function getCurrentUser(): ?User
    {
        return $this->security->getUser();
    }

    /**
     * Renders the account page for the current authenticated user.
     *
     * This method retrieves the current authenticated user and renders the
     * account page using a Twig template.
     *
     * @return Response The response object containing the rendered HTML content
     *
     * @throws RuntimeException If the Twig rendering fails
     */
    public function renderAccountPage(): Response
    {
        $user = $this->getCurrentUser();

        return new Response($this->twig->render('account/account.html.twig', ['user' => $user]));
    }

    /**
     * Handles the account editing process.
     *
     * @param Request $request The current request object
     *
     * @return Response The response object rendering the account edit or error page
     *
     * @throws \RuntimeException If an error occurs during form handling or entity persistence
     */
    public function handleAccountEdit(Request $request): array
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            return ['status' => 'access_denied'];
        }

        $originalRoles = $user->getRoles();

        $form = $this->formFactory->create(AccountType::class, $user, [
            'is_admin' => in_array('ROLE_ADMIN', $user->getRoles(), true),
        ]);

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return [
                'status' => 'form_invalid',
                'form' => $form,
            ];
        }

        $newRoles = $form->has('roles')
            ? $form->get('roles')->getData()
            : $user->getRoles();

        $wasAdmin = in_array('ROLE_ADMIN', $originalRoles, true);
        $isAdminNow = in_array('ROLE_ADMIN', $newRoles, true);

        if ($wasAdmin && !$isAdminNow) {
            $adminCount = $this->adminRepository->countOtherAdmins($user);

            if (0 === $adminCount) {
                return ['status' => 'admin_error'];
            }
        }

        $plainPassword = $form->get('plainPassword')->getData();

        if ($plainPassword) {
            $encodedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userRepository->save($user);
        }

        return ['status' => 'success'];
    }
}
