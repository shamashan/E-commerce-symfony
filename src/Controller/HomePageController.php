<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomePageController extends AbstractController
{
    #[Route('/', name: 'app_home_page')]
    public function index(CategoryRepository $categoryRepo, SubCategoryRepository $subCategoryRepo, ProductRepository $productRepo, PaginatorInterface $paginator, Request $request): Response
    {
        $categories = $categoryRepo->findAll();
        // $products = $productRepo->findAll();
        $subCategories = $subCategoryRepo->findAll();
        $lastProductsAdded = $productRepo->findBy([], ["id" => "DESC"], 5);
        $data  = $productRepo->findBy([], ["id" => "DESC"]);
        $products = $paginator->paginate($data, $request->query->getInt("page", 1), 6);

        return $this->render('home_page/index.html.twig', [
            'controller_name' => 'HomePageController',
            'categories' => $categories,
            "products" => $products,
            "lastProductsAdded" => $lastProductsAdded,
            "subCategories" => $subCategories,
        ]);
    }

    #[Route('/product/{id}/show/', name: 'app_product_show')]
    public function showProduct(Product $product,  ProductRepository $productRepo, CategoryRepository $categoryRepo): Response
    {
        $products = $productRepo->findAll();
        return $this->render('home_page/show.html.twig', [
            'product' => $product,
            // "products" => $products,
            "categories" => $categoryRepo->findAll(),
        ]);
    }

    #[Route('/product/subcategory/{id}/filter', name: 'app_home_product_filter', methods: ['GET'])]
    public function filter($id, CategoryRepository $categoryRepo, SubCategoryRepository $subCategoryRepo): Response
    {
        $products = $subCategoryRepo->find($id)->getProducts();
        $subCategory = $subCategoryRepo->find($id);
        return $this->render('home_page/filter.html.twig', [
            'products' => $products,
            'subCategory' => $subCategory,
            "categories" => $categoryRepo->findAll(),
        ]);
    }

    #[Route('/product/category/{id}/filter', name: 'app_home_category_filter', methods: ['GET'])]
    public function filterCategories($id, ProductRepository $productRepo): Response
    {
        $products = $productRepo->findBy(["category" => $id]);
        return $this->render('home_page/filter.html.twig', [
            'products' => $products,
        ]);
    }
}
