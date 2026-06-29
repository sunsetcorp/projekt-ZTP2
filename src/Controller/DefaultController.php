<?php

/**
 * Default controller.
 */

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use App\Service\CategoryServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController.
 */
class DefaultController extends AbstractController
{
    /**
     * @param CategoryServiceInterface $categoryService The category service interface
     */
    public function __construct(private readonly CategoryServiceInterface $categoryService)
    {
    }

    /**
     * Renders the homepage.
     *
     * @return Response HTTP response
     *
     * */
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('base.html.twig', [
            'categories' => $this->categoryService->getAllCategories(),
        ]);
    }
}
