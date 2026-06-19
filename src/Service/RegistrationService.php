<?php

/**
 * Registration service.
 */

namespace App\Service;

use App\Entity\User;
use App\Form\Type\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class RegistrationService.
 *
 * Service responsible for user registration.
 */
class RegistrationService implements RegistrationServiceInterface
{
    /**
     * RegistrationService constructor.
     *
     * @param EntityManagerInterface      $entityManager     The entity manager
     * @param FormFactoryInterface        $formFactory       The form factory
     * @param UserPasswordHasherInterface $passwordHasher    The password hasher
     * @param UserAuthenticatorInterface  $userAuthenticator The user authenticator
     * @param LoginFormAuthenticator      $authenticator     The login form authenticator
     * @param Security                    $security          The security service
     * @param UserRepository              $userRepository    The user repository
     */
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly FormFactoryInterface $formFactory, private readonly UserPasswordHasherInterface $passwordHasher, private readonly UserAuthenticatorInterface $userAuthenticator, private readonly LoginFormAuthenticator $authenticator, private readonly Security $security, private readonly UserRepository $userRepository)
    {
    }

    /**
     * Registers a new user.
     *
     * @param User    $user    The user entity to register
     * @param Request $request The request object containing form data
     *
     * @return bool Returns true if registration was successful, false otherwise
     */
    public function register(User $user, Request $request): bool
    {
        $form = $this->formFactory->create(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('password')->getData()));

                $this->userRepository->save($user);

                $this->userAuthenticator->authenticateUser($user, $this->authenticator, $request);

                return true;
            } catch (\Throwable) {
                return false;
            }
        }


        return false;
    }
}
