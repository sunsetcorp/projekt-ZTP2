<?php

/**
 * Cover controller.
 */

namespace App\Controller;

use App\Entity\Cover;
use App\Entity\Album;
use App\Form\Type\CoverType;
use App\Service\CoverServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class CoverController.
 */
#[Route('/cover')]
class CoverController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param CoverServiceInterface $coverService Cover service
     * @param TranslatorInterface   $translator   Translator
     */
    public function __construct(private readonly CoverServiceInterface $coverService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     * @param Album   $album   Album entity
     *
     * @return Response HTTP response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/cover/create/{id}', name: 'cover_create', methods: ['GET', 'POST'])]
    public function create(Request $request, Album $album): Response
    {
        $existingCover = $this->coverService->findByAlbum($album);
        if ($existingCover) {
            return $this->redirectToRoute('cover_edit', [
                'id' => $existingCover->getId(),
            ]);
        }

        $cover = new Cover();
        $form = $this->createForm(CoverType::class, $cover);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $this->coverService->create($file, $cover, $album);


            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('album_show', [
                'id' => $album->getId(),
            ]);
        }

        return $this->render(
            'cover/create.html.twig',
            [
                'form' => $form->createView(),
                'album' => $album,
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Cover   $cover   Cover entity
     *
     * @return Response HTTP response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route(
        '/{id}/edit',
        name: 'cover_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'POST']
    )]
    public function edit(Request $request, Cover $cover): Response
    {
        /** @var Album $album */
        $album = $cover->getAlbum();

        $form = $this->createForm(
            CoverType::class,
            $cover,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('cover_edit', ['id' => $cover->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            $this->coverService->update(
                $file,
                $cover,
                $album
            );

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('album_show', [
                'id' => $album->getId(),
            ]);
        }

        return $this->render(
            'cover/edit.html.twig',
            [
                'form' => $form->createView(),
                'cover' => $cover,
            ]
        );
    }
}
