<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private $session;
    private $productRepository;

    public function __construct(SessionInterface $session, ProductRepository $repoProduct)
    {
        $this->session = $session;
        $this->productRepository = $repoProduct;
    }

    public function add(int $id)
    {
        $cart = $this->session->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        }else{
            $cart[$id] = 1;
        }

        $this->session->set('cart', $cart);
    }

    public function remove(int $id)
    {
        $cart = $this->session->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]--;
            if ($cart[$id] === 0) {
                unset($cart[$id]);
            }
        }else{
            $cart[$id] = 1;
        }

        $this->session->set('cart', $cart);
    }

    public function delete(int $id)
    {
        $cart = $this->session->get('cart', []);

        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }

        $this->session->set('cart', $cart);
    }

    public function getProductDataByCart()
    {
        $cartProducts = [];

        foreach ($this->session->get('cart', []) as $id => $qty) {
            $cartProducts[] = array(
                'product' => $this->productRepository->find($id),
                'quantity' => $qty
            );
        }

        return $cartProducts;
    }

    public function getTotalPrice(array $cartProducts)
    {
        $total = 0;

        foreach ($cartProducts as $product) {
            $totalProduct = $product['quantity'] * $product['product']->getPrice();
            $total += $totalProduct;
        }

        return $total;
    }
}