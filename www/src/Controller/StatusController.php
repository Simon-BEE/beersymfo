<?php

namespace App\Controller;

use App\Entity\Status;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class StatusController extends AbstractController
{
    /**
     * @Route("/status", name="status")
     */
    public function index(ObjectManager $manager)
    {
        dd('status terminé');
        $status = new Status();

        //$status->setName('En attente de paiement');
        //$status->setName('En préparation');
        //$status->setName('Expédiée');
        //$status->setName('Terminé');

        $manager->persist($status);
        $manager->flush();

        return $this->render('status/index.html.twig', [
            'controller_name' => 'Ajouter des status',
        ]);
    }
}
