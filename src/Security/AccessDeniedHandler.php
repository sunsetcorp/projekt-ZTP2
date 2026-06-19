<?php

/**
 * Acces denied handler.
 */

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

/**
 * Class AccessDeniedHandler.
 *
 * Handles access denied exceptions by redirecting the user to a specific route.
 */
class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    /**
     * AccessDeniedHandler constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator The URL generator service
     */
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * Handles an access denied exception.
     *
     * Redirects the user to the 'access_denied' route.
     *
     * @param Request               $request               The request object
     * @param AccessDeniedException $accessDeniedException The access denied exception
     *
     * @return Response|null A Response instance, or null if implementation does not handle it
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('access_denied'));
    }
}
