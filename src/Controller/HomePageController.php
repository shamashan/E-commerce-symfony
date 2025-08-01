<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomePageController extends AbstractController
{
    #[Route('/', name: 'app_home_page')]
    public function index(CategoryRepository $repo): Response
    {

        $categories = $repo->findAll();
        return $this->render('home_page/index.html.twig', [
            'controller_name' => 'HomePageController',
            'categories' => $categories,
        ]);
    }
}
