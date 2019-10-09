<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    public function __construct(SessionInterface $session)
    {
        if (empty($session->get('cart', []))) {
            return $this->redirectToRoute('cart_index');
        }
    }
    /**
     * @Route("/order/payment", name="order_payment")
     */
    public function payment()
    {
        return $this->render('order/payment.html.twig', [
            'title' => 'Paiement',
        ]);
    }

    /**
     * @Route("/order/confirmation", name="order_confirmation")
     */
    public function confirmation()
    {
        return $this->render('order/confirmation.html.twig', [
            'title' => 'Confirmation de la commande',
        ]);
    }
}
