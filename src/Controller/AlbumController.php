<?php

/**
 * Album controller.
 */

namespace App\Controller;

use App\Service\AlbumService;
use App\Form\Type\AlbumType;
use App\Entity\Album;
use App\Entity\Comment;
use App\Entity\User;
use App\Form\Type\CommentType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;

/**
 * Class AlbumController.
 */
#[Route('/')]
class AlbumController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param AlbumService        $albumService The album service
     * @param TranslatorInterface $translator   The translator
     */
    public function __construct(private readonly AlbumService $albumService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param PaginatorInterface $paginator Paginator
     * @param Request            $request   HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/', name: 'album_index', methods: ['GET'])]
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $phrase = $request->query->get('phrase');
        $page = $request->query->getInt('page', 1);
        $categoryId = $request->query->getInt('category', 0);

        $pagination = $this->albumService->getPaginatedList($page, $categoryId, $phrase);

        return $this->render('album/index.html.twig', [
            'pagination' => $pagination,
            'phrase' => $phrase,
        ]);
    }

    /**
     * Remove favorite action.
     *
     * @param int $id Album ID
     *
     * @return Response HTTP response
     *
     * */
    #[Route('/album/{id}/remove-favorite', name: 'remove_favorite')]
    public function removeFavorite(int $id): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        if ($user?->isBlocked()) {
            $this->addFlash(
                'danger',
                $this->translator->trans('message.youreblockedfav')
            );

            return $this->redirectToRoute('album_show', ['id' => $id]);
        }

        try {
            $this->albumService->removeFavorite($id, $user);
            $this->addFlash('success', $this->translator->trans('message.removedFav'));
        } catch (\InvalidArgumentException) {
            $this->addFlash('error', $this->translator->trans('message.doesnotexist'));
        }

        return $this->redirectToRoute('app_account', ['id' => $id]);
    }

    /**
     * Albums by tag action.
     *
     * @param int $id Tag ID
     *
     * @return Response HTTP response
     * */
    #[Route('/albums/tag/{id}', name: 'album_by_tag', methods: ['GET'])]
    public function albumsByTag(int $id): Response
    {
        try {
            $tag = $this->albumService->getTagById($id);
        } catch (\InvalidArgumentException) {
            throw $this->createNotFoundException($this->translator->trans('message.tagdoesnotexist'));
        }

        return $this->render('album/by_tag.html.twig', [
            'tag' => $tag,
            'albums' => $this->albumService->getAlbumsByTag($tag),
        ]);
    }

    /**
     * Favorite action.
     *
     * @param int      $id       Album ID
     * @param Security $security Security component
     *
     * @return Response HTTP response
     *
     * */
    #[Route('/album/{id}/favorite', name: 'favorite_album', methods: ['POST'])]
    public function favorite(int $id, Security $security): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($user?->isBlocked()) {
            $this->addFlash(
                'danger',
                $this->translator->trans('message.youreblockedfav')
            );

            return $this->redirectToRoute('album_show', ['id' => $id]);
        }

        $result = $this->albumService->toggleFavoriteById($id, $user);
        $this->addFlash('success', $this->translator->trans($result));

        return $this->redirectToRoute('album_show', ['id' =>  $id]);
    }

    /**
     * Show action.
     *
     * @param Album $album Album entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'album_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    #[IsGranted('VIEW', subject: 'album')]
    public function show(Album $album): Response
    {
        $album = $this->albumService->getDetailedAlbum($album);
        $commentForm = $this->createForm(CommentType::class, new Comment());
        $user = $this->getUser();

        return $this->render('album/show.html.twig', [
            'album' => $album,
            'cover' => $this->albumService->getCover($album),
            'comment_form' => $commentForm->createView(),
            'comments' => $this->albumService->getComments($album),
            'averageRating' => $this->albumService->getAverageRating($album),
            'userRating' => $this->albumService->getUserRating($album, $user),
        ]);
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     *
     **/
    #[Route('/create', name: 'album_create', methods: 'GET|POST')]
    #[IsGranted('CREATE', subject: 'album')]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException($this->translator->trans('message.accessdenied'));
        }
        $this->denyAccessUnlessGranted('NOT_BLOCKED');
        $user = $this->getUser();
        $album = new Album();
        $album->setAuthor($user);
        $form = $this->createForm(AlbumType::class, $album, ['action' => $this->generateUrl('album_create')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->albumService->save($album);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('album_index');
        }

        return $this->render('album/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Album   $album   Album entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'album_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|POST')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Album $album): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException($this->translator->trans('message.accessdenied'));
        }
        $this->denyAccessUnlessGranted('NOT_BLOCKED');

        $form = $this->createForm(AlbumType::class, $album, ['method' => 'POST', 'action' => $this->generateUrl('album_edit', ['id' => $album->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->albumService->save($album);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('album_index');
        }

        return $this->render(
            'album/edit.html.twig',
            [
                'form' => $form->createView(),
                'album' => $album,
                'cover' => $this->albumService->getCover($album),
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Album   $album   Album entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'album_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|POST')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Album $album): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException($this->translator->trans('message.accessdenied'));
        }

        $this->denyAccessUnlessGranted('NOT_BLOCKED');

        $form = $this->createForm(
            FormType::class,
            $album,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('album_delete', ['id' => $album->getId()]),
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->albumService->delete($album);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('album_index');
        }

        return $this->render(
            'album/delete.html.twig',
            [
                'form' => $form->createView(),
                'album' => $album,
            ]
        );
    }

    /**
     * Show top-rated Albums.
     *
     * @return Response HTTP response
     */
    #[Route('/top-rated', name: 'top_rated')]
    public function topRated(): Response
    {

        $albums = $this->albumService->getTopRatedAlbums();

        return $this->render('album/top-rated.html.twig', [
            'albums' => $albums,
        ]);
    }
}
