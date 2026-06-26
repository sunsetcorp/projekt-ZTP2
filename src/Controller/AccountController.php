<?php

/**
 * Account controller.
 */

namespace App\Controller;

use App\Service\AccountServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AccountController.
 */
class AccountController extends AbstractController
{
    /**
     * AccountController constructor.
     *
     * @param AccountServiceInterface $accountService The account service used for business logic
     * @param TranslatorInterface     $translator     The translator
     */
    public function __construct(private readonly AccountServiceInterface $accountService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Render the account page.
     *
     * @return Response The response object
     *
     * @Route("/account", name="app_account")
     */
    #[\Symfony\Component\Routing\Attribute\Route('/account', name: 'app_account')]
    public function account(): Response
    {
        return $this->accountService->renderAccountPage();
    }

    /**
     * Handle account edit request.
     *
     * @param Request $request The HTTP request object
     *
     * @return Response The response object
     */
    #[\Symfony\Component\Routing\Attribute\Route('/account/edit', name: 'app_account_edit')]
    public function edit(Request $request): Response
    {
        $result = $this->accountService->handleAccountEdit($request);

        return match ($result['status']) {
            'access_denied' => $this->render('error/accessdenied.html.twig'),

            'form_invalid' => $this->render('account/edit.html.twig', [
                'accountForm' => $result['form']->createView(),
            ]),

            'admin_error' => (function (): Response {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('message.admin_error')
                );

                return $this->redirectToRoute('app_account_edit');
            })(),

            'success' => (function (): Response {
                $this->addFlash(
                    'success',
                    $this->translator->trans('message.edited_successfully')
                );

                return $this->redirectToRoute('app_account');
            })(),
        };
    }
}
