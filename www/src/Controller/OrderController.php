<?php

namespace App\Controller;

use App\Entity\Command;
use App\Entity\User;
use App\Repository\StatusRepository;
use App\Repository\AddressRepository;
use App\Repository\CommandRepository;
use App\Repository\OrderLineRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    private $session;
    private $orderLineRepository;
    private $statusRepository;
    private $addressRepository;
    private $manager;

    public function __construct(
        SessionInterface $session, 
        OrderLineRepository $orderLineRepository, 
        StatusRepository $statusRepository, 
        AddressRepository $addressRepository,
        ObjectManager $manager)
    {
        $this->session = $session;
        $this->orderLineRepository = $orderLineRepository;
        $this->statusRepository = $statusRepository;
        $this->addressRepository = $addressRepository;
        $this->manager = $manager;
    }

    /**
     * @Route("/order/payment", name="order_payment")
     */
    public function payment()
    {
        if (empty($this->session->get('cart', []))) {
            return $this->redirectToRoute('cart_index');
        }

        // TODO: Implement feature to do a payment
        return $this->render('order/payment.html.twig', [
            'title' => 'Paiement',
        ]);
    }

    /**
     * @Route("/order/resume/{id}", name="order_resume")
     */
    public function resume(Command $command)
    {
        if ($this->getUser()->getId() !== $command->getUser()->getId()) {
            return $this->redirectToRoute('home');
        }

        $orderLines = $this->orderLineRepository->findBy(['token' => $command->getToken()]);
        $total = $command->getPrice();

        return $this->render('order/resume.html.twig', [
            'title' => 'Récapitulatif de la commande n°'.$command->getId(),
            'orderLines' => $orderLines,
            'total' => $total,
            'address' => $command->getAddress()
        ]);
    }

    /**
     * @Route("/order/resume/all/{id}", name="order_all")
     */
    public function allByUser(User $user, CommandRepository $commandRepository)
    {
        if (!$this->getUser() || $this->getUser()->getId() !== $user->getId()) {
            return $this->redirectToRoute('home');
        }
        
        return $this->render('order/all.html.twig', [
            'title' => 'Toutes vos commandes',
            'commands' => $commandRepository->findBy(['user' => $user->getId()])
        ]);
    }

    /**
     * @Route("/order/purge", name="order_purge")
     */
    public function purgeDatabase()
    {
        // TODO: supprimer toutes les OrderLines non utilisées (donc dans aucune commande)
    }

    /**
     * @Route("/order/confirmation", name="order_confirmation")
     */
    public function confirmation(CommandRepository $commandRepository)
    {
        if (empty($this->session->get('cart', []))) {
            return $this->redirectToRoute('cart_index');
        }

        $this->addInDatabase();
        sleep(2);

        $command = $commandRepository->findOneBy(['user' => $this->getUser()->getId()], ['id' => 'desc']);
        $this->addFlash('success', 'Votre commande a bien été validé, nous nous en occuperons dans les plus brefs délais');
        return $this->redirectToRoute('order_resume', ['id' => $command->getId()]);
    }

    private function addInDatabase()
    {
        $futureOrder = $this->orderLineRepository->findBy(['token' => $this->session->get('cart_token')]);

        $total = 0;
        foreach ($futureOrder as $value) {
            $total += $value->getQuantity() * $value->getPrice();
        }

        
        $total = (float)number_format($total, 2);
        
        $order = new Command();
        
        $order->setUser($this->getUser())
            ->setAddress($this->addressRepository->findOneBy(
                ['user' => $this->getUser()->getId(),
                'is_default' => true]))
            ->setStatus($this->statusRepository->find(1))
            ->setToken($this->session->get('cart_token'))
            ->setTva(1.2)
            ->setPrice($total)
            ->setCreatedAt(new \DateTime());
            
        if ($total < 30) {
            $order->setShippingFees(4.99);
        }else{
            $order->setShippingFees(0);
        }
        
        $this->manager->persist($order);
        $this->manager->flush();

        $this->session->remove('cart');
        $this->session->remove('cart_token');
    }
}
