<?php

namespace App\Service;

use App\Entity\OrderLine;
use App\Repository\ProductRepository;
use App\Repository\OrderLineRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CartService
{
    private $session;
    private $productRepository;
    private $repoOrderLine;
    private $tokenService;
    private $manager;
    private $cart;
    private $user;

    public function __construct(
        SessionInterface $session, 
        ProductRepository $repoProduct, 
        OrderLineRepository $repoOrderLine, 
        TokenService $tokenService, 
        ObjectManager $manager,
        TokenStorageInterface $tokenStorage)
    {
        $this->session = $session;
        $this->productRepository = $repoProduct;
        $this->repoOrderLine = $repoOrderLine;
        $this->tokenService = $tokenService;
        $this->manager = $manager;
        $this->cart = $this->session->get('cart', []);
        $this->user = $tokenStorage->getToken()->getUser();
    }

    public function add(int $id)
    {

        if (empty($this->session->get('cart_token', []))) {
            $this->session->set('cart_token', $this->tokenService->generateToken());
        }

        if (!empty($this->cart[$id])) {
            $this->cart[$id]++;
        }else{
            $this->cart[$id] = 1;
        }

        $this->addInDatabase($id);

        $this->session->set('cart', $this->cart);
    }

    public function remove(int $id)
    {
        if (!empty($this->cart[$id])) {
            $this->cart[$id]--;

            if ($this->cart[$id] === 0) {
                unset($this->cart[$id]);

                $this->deleteFromDatabase($id);
            }else{
                $this->addInDatabase($id);
            }

        }

        $this->removeToken();

        $this->session->set('cart', $this->cart);
    }

    public function delete(int $id)
    {
        if (!empty($this->cart[$id])) {
            unset($this->cart[$id]);
        }

        $this->deleteFromDatabase($id);

        $this->removeToken();

        $this->session->set('cart', $this->cart);
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

    private function addInDatabase(int $id)
    {
        $product = $this->productRepository->find($id);

        $token = $this->session->get('cart_token', []);

        // Si une ligne du panier existe avec le même token et le même produit on utilise cette objet sinon on en crée une nouvelle
        if ($this->repoOrderLine->findOneBy(['token' => $token, 'product' => $product->getId()])) {
            $orderLine = $this->repoOrderLine->findOneBy(['token' => $token, 'product' => $product->getId()]);
        }else{
            $orderLine = (new OrderLine())->setToken($token);
        }


        if (!$orderLine->getProduct() || $orderLine->getProduct()->getId() !== $product->getId()) {
            $orderLine->setProduct($product);
        }

        $orderLine->setQuantity($this->cart[$id])
            ->setPrice($product->getPrice());
        //dd($this->user);
        if ($this->user && !is_string($this->user)) {
            $orderLine->setUser($this->user);
        }

        $this->manager->persist($orderLine);
        $this->manager->flush();
    }

    private function deleteFromDatabase(int $id)
    {
        $product = $this->productRepository->find($id);
        $token = $this->session->get('cart_token', []);
        $orderLine = $this->repoOrderLine->findOneBy(['token' => $token, 'product' => $product->getId()]);

        $this->manager->remove($orderLine);
        $this->manager->flush();
    }

    private function removeToken()
    {
        if (empty($this->cart)) {
            $this->session->remove('token');
        }
    }
}