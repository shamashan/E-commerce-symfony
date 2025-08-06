<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController


{

    public function __construct(private readonly ProductRepository $productRepository) {}

    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $cartWithData = [];
        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                "product" => $this->productRepository->find($id),
                "quantity" => $quantity,
            ];
        }
        $total = array_sum(array_map(function ($item) {
            return $item['quantity'] * $item['product']->getPrice();
        }, $cartWithData));
        // dd($cartWithData);
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'items' => $cartWithData,
            'total' => $total,
        ]);
    }


    #[Route('/cart/add/{id}/', name: 'app_cart_new', methods: ['GET'])]
    public function addProductToCart(Request $request, $id, SessionInterface $session): Response
    {

        $cart = $session->get('cart', []);
        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/delete/{id}/', name: 'app_cart_item_delete', methods: ['GET'])]
    public function deleteProductFromCart(Request $request, $id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);
        return $this->redirectToRoute('app_cart');
    }


    #[Route('/cart/delete', name: 'app_cart_delete', methods: ['GET'])]
    public function deleteCart(SessionInterface $session): Response
    {

        $session->set('cart', []);
        return $this->redirectToRoute('app_cart');
    }
}
