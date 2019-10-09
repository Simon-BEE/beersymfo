<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\AddressRepository;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartController extends AbstractController
{
    private $addressRepository;

    public function __construct(AddressRepository $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }
    /**
     * @Route("/cart", name="cart_index")
     */
    public function index(CartService $cartService)
    {

        $cartProducts = $cartService->getProductDataByCart();
        $total = $cartService->getTotalPrice($cartProducts);

        return $this->render('cart/index.html.twig', [
            'title' => 'Votre panier',
            'cartProducts' => $cartProducts,
            'total' => $total
        ]);
    }

    /**
     * @Route("/cart/validate", name="cart_validation")
     */
    public function validation(CartService $cartService, SessionInterface $session)
    {
        if (empty($session->get('cart', []))) {
            return $this->redirectToRoute('cart_index');
        }

        if (!$this->getUser()) {
            $this->addFlash('error', 'Veuillez vous connecter avant de procéder à la commande');
            return $this->redirectToRoute('security_login');
        }

        $cartProducts = $cartService->getProductDataByCart();
        $total = $cartService->getTotalPrice($cartProducts);

        $address = $this->getAddress();

        return $this->render('cart/validation.html.twig', [
            'title' => 'Validation du panier',
            'cartProducts' => $cartProducts,
            'total' => $total,
            'address' => $address
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="cart_add")
     */
    public function add(int $id, CartService $cartService)
    {
        $cartService->add($id);
        
        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/cart/remove/{id}", name="cart_remove")
     */
    public function remove(int $id, CartService $cartService)
    {
        $cartService->remove($id);
        
        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/cart/delete/{id}", name="cart_delete")
     */
    public function delete(int $id, CartService $cartService)
    {
        $cartService->delete($id);
        
        return $this->redirectToRoute('cart_index');
    }

    private function getAddress()
    {
        return $this->addressRepository->findOneBy(array(
            'user' => $this->getUser()->getId(),
            'is_default' => true
        ));
    }
}
