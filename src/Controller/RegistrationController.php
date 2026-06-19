<?php

/**
 * Registration controller.
 */

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Form\Type\RegistrationFormType;
use App\Service\RegistrationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RegistrationController.
 */
class RegistrationController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param RegistrationServiceInterface $registrationService Registration service
     * @param TranslatorInterface          $translator          The translator
     */
    public function __construct(private readonly RegistrationServiceInterface $registrationService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Handles the user registration process.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->registrationService->register($user, $request)) {
                return $this->redirectToRoute('album_index');
            }
            $this->addFlash('error', $this->translator->trans('message.failRegister'));
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
