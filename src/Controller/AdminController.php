<?php

/**
 * Admin controller.
 */

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use App\Entity\User;
use App\Form\Type\UserEditType;
use App\Service\AdminServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AdminController.
 */
class AdminController extends AbstractController
{
    /**
     * AdminController constructor.
     *
     * @param AdminServiceInterface $adminService The admin service used for business logic
     * @param TranslatorInterface   $translator   The translator
     */
    public function __construct(private readonly AdminServiceInterface $adminService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Display the list of users for administrators.
     *
     * @param int $page The page number for pagination
     *
     * @return Response The response object
     *
     * */
    #[Route('/admin/users', name: 'admin_user_list')]
    public function userList(#[MapQueryParameter] int $page = 1): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $limit = 10;
        $pagination = $this->adminService->getPaginatedUsers($page, $limit);

        return $this->render('admin/user_list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Edit user details by administrators.
     *
     * @param Request                     $request        The HTTP request object
     * @param User                        $user           The user entity to edit
     * @param UserPasswordHasherInterface $passwordHasher The password hasher service
     *
     * @return Response The response object
     */
    #[Route('/admin/users/edit/{id}', name: 'admin_user_edit')]
    public function editUser(Request $request, User $user, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->getUser()?->isBlocked()) {
            $this->addFlash(
                'danger',
                $this->translator->trans('message.cantedit')
            );

            return $this->redirectToRoute('admin_user_list');
        }
        $form = $this->createForm(UserEditType::class, $user);

        $originalRoles = $user->getRoles();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newRoles = $form->get('roles')->getData();

            $wasAdmin = in_array('ROLE_ADMIN', $originalRoles, true);
            $isAdminNow = in_array('ROLE_ADMIN', $newRoles, true);

            if ($wasAdmin && !$isAdminNow) {
                $otherAdminsCount = $this->adminService->countOtherAdmins($user);

                if (0 === $otherAdminsCount) {
                    $this->addFlash('danger', $this->translator->trans('message.admin_error'));

                    return $this->redirectToRoute('admin_user_edit', [
                        'id' => $user->getId(),
                    ]);
                }
            }

            $plainPassword = $form->get('plainPassword')->getData();

            if ($plainPassword) {
                $this->adminService->updateUserPassword($user, $plainPassword);
            } else {
                $this->adminService->updateUser($user);
            }

            $this->addFlash('success', $this->translator->trans('message.edited_successfully'));

            return $this->redirectToRoute('admin_user_list');
        }

        return $this->render('admin/edit.html.twig', [
            'userForm' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Blocking users by administrators.
     *
     * @param User $user User
     *
     * @return Response Redirects to the user list
     */
    #[Route('/admin/users/block/{id}', name: 'admin_user_block')]
    public function blockUser(User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->getUser()?->getId() === $user->getId()) {
            $this->addFlash(
                'danger',
                $this->translator->trans('message.cantblock')
            );

            return $this->redirectToRoute('admin_user_list');
        }
        if ($this->getUser()?->isBlocked()) {
            $this->addFlash(
                'danger',
                $this->translator->trans('message.youreblockedfav')
            );

            return $this->redirectToRoute('admin_user_list');
        }

        $user->setIsBlocked(true);

        $this->adminService->updateUser($user);

        $this->addFlash('success', $this->translator->trans('message.blocked'));

        return $this->redirectToRoute('admin_user_list');
    }

    /**
     * Unblocking users by administrators.
     *
     * @param User $user User
     *
     * @return Response Redirects to the user list
     */
    #[Route('/admin/users/unblock/{id}', name: 'admin_user_unblock')]
    public function unblockUser(User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->getUser()?->getId() === $user->getId()) {
            $this->addFlash('danger', $this->translator->trans('message.cantunblock'));

            return $this->redirectToRoute('admin_user_list');
        }

        if ($this->getUser()?->isBlocked()) {
            $this->addFlash(
                'danger',
                $this->translator->trans('message.youreblockedfav')
            );

            return $this->redirectToRoute('admin_user_list');
        }

        $user->setIsBlocked(false);

        $this->adminService->updateUser($user);

        $this->addFlash('success', $this->translator->trans('message.unblocked'));

        return $this->redirectToRoute('admin_user_list');
    }
}
