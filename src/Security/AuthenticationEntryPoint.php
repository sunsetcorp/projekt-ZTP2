<?php

/**
 * Authentication entry point.
 */

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AuthenticationEntryPoint.
 *
 * This class implements the AuthenticationEntryPointInterface to handle
 * authentication exceptions by redirecting the user to the login page with
 * a flash message.
 */
class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * AuthenticationEntryPoint constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator The URL generator service
     * @param TranslatorInterface   $translator   The translator interface
     */
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Starts the authentication process.
     *
     * Redirects the user to the login page and adds a flash message notifying
     * the user to log in to access the requested page.
     *
     * @param Request                      $request       The request object
     * @param AuthenticationException|null $authException The authentication exception (optional)
     *
     * @return RedirectResponse A redirect response to the login page
     */
    public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
    {
        $request->getSession()->getFlashBag()->add('note', $this->translator->trans('message.loginaccess'));

        return new RedirectResponse($this->urlGenerator->generate('security_login'));
    }
}
