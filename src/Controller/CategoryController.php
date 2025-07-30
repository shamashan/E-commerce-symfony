<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    #[Route('/admin/category', name: 'app_category')]
    public function index(CategoryRepository $repo): Response
    {
        $categories = $repo->findAll();
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories
        ]);
    }

    #[Route('/category/add', name: 'app_category_add')]
    public function addCategory(EntityManagerInterface $entityManager, Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', 'The category was added successfully!!');
            return $this->redirectToRoute('app_category');
        }
        return $this->render('category/newCategory.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/category/update/{id}', name: 'app_category_update')]
    public function editCategory(EntityManagerInterface $entityManager, Request $request, Category $category): Response
    {
        // $category = $entityManager->getRepository(Category::class)->find($id);
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'The category was updated successfully!');
            return $this->redirectToRoute('app_category');
        }
        return $this->render('category/updateCategory.html.twig', [
            'form' => $form->createView()
        ]);
    }



    #[Route('/category/delete/{id}', name: 'app_category_delete')]
    public function deleteCategory(EntityManagerInterface $entityManager, $id, Request $request, Category $category): Response
    {
        // $category = $entityManager->getRepository(Category::class)->find($id);
        $entityManager->remove($category);
        $entityManager->flush();
        $this->addFlash("success", 'The category was deleted!');
        return $this->redirectToRoute("app_category");
    }
}
