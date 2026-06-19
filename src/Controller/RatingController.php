<?php

/**
 * Rating controller.
 */

namespace App\Controller;

use App\Entity\Album;
use App\Service\RatingServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RatingController.
 */
class RatingController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator The translator
     */
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Add rating action.
     *
     * @param Album                  $album         Album entity
     * @param Request                $request       HTTP request
     * @param RatingServiceInterface $ratingService Rating Service Interface
     *
     * @return Response HTTP response
     **/
    #[Route('/album/{id}/rate', name: 'album_rate', methods: ['POST'])]
    public function rate(Album $album, Request $request, RatingServiceInterface $ratingService): Response
    {

        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        if ($user->isBlocked()) {
            $this->addFlash(
                'danger',
                $this->translator->trans('message.youreblockedfav')
            );

            return $this->redirectToRoute('album_show', [
                'id' => $album->getId(),
            ]);
        }

        $value = (int) $request->request->get('rating');

        if ($value < 1 || $value > 5) {
            $this->addFlash(
                'danger',
                'Invalid rating.'
            );

            return $this->redirectToRoute('album_show', [
                'id' => $album->getId(),
            ]);
        }

        $ratingService->rate($album, $user, $value);

        $this->addFlash(
            'success',
            $this->translator->trans('message.successfullyrated')
        );

        return $this->redirectToRoute('album_show', [
            'id' => $album->getId(),
        ]);
    }
}
