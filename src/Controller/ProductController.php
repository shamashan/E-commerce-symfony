<?php

namespace App\Controller;

use App\Entity\AddProductHistory;
use App\Entity\Product;
use App\Form\AddProductHistoryType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/editor/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #region Add
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            if ($image) {
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeImageName = $slugger->slug($originalName);
                $newFileImageName = $safeImageName . "-" . uniqid() . '.' . $image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('image_directory'),
                        $newFileImageName
                    );
                } catch (FileException $err) {
                }
                $product->setImage($newFileImageName);
            }


            $entityManager->persist($product);
            $entityManager->flush();

            $stockHistory = new AddProductHistory();
            $stockHistory->setProduct($product);
            $stockHistory->setQuantity($product->getStock());
            $stockHistory->setCreatedAt(new DateTimeImmutable());
            $entityManager->persist($stockHistory);
            $entityManager->flush();

            $this->addFlash('success', 'The product was added successfully!!');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }
    #endregion


    #region Show
    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
    #endregion

    #region Edit
    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'The product was updates successfully!!');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }
    #endregion

    #region Delete
    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'The product was added successfully!!');
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
    #endregion

    #[Route('/add/product/{id}/', name: 'app_product_stock_add',  methods: ['GET', 'POST'])]
    public function stockAdd($id, EntityManagerInterface $entityManager, ProductRepository $productRepository, Request $request): Response
    {
        $stockAdd = new AddProductHistory();
        $form = $this->createForm(AddProductHistoryType::class, $stockAdd);
        $form->handleRequest($request);

        $product = $productRepository->find($id);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($stockAdd->getQuantity() > 0) {
                $newQuantity = $product->getStock() + $stockAdd->getQuantity();
                $product->setStock($newQuantity);
                $stockAdd->setCreatedAt(new DateTimeImmutable());
                $stockAdd->setProduct($product);
                $entityManager->persist($stockAdd);
                $entityManager->flush();

                $this->addFlash('success', 'The product was modified successfully!!');
                return $this->redirectToRoute('app_product_index');
            } else {
                $this->addFlash('error', 'The product was not modified!!');
                return $this->redirectToRoute('app_product_stock_add', ['id' => $product->getId()]);
            }
        }





        return $this->render('product/addStock.html.twig', [
            'form' => $form->createView(),
            "product" => $product,
        ]);
    }
}
