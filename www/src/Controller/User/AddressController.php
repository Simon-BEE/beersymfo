<?php

namespace App\Controller\User;

use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\AddressRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AddressController extends AbstractController
{
    /**
     * @Route("/profile/address/new", name="address_new", methods={"GET","POST"})
     */
    public function new(Request $request, AddressRepository $addressRepository): Response
    {
        $nbAddress = 0;
        foreach ($this->getUser()->getAddresses() as $key => $value) {
            dump($key);
            $nbAddress += 1;
        }
        
        if ($nbAddress === 3) {
            $this->addFlash('error', 'Veuillez supprimer une adresse avant d\'en rajouter une nouvelle.');
            return $this->redirectToRoute('user_profile');
        }

        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);
        
        $entityManager = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($this->getUser());

            if ($address->getIsDefault() === true) {
                foreach ($addressRepository->findAll() as $key => $value) {
                    $value->setIsDefault(false);
                    $entityManager->persist($value);
                }
            }

            if ($nbAddress === 0) {
                $address->setIsDefault(true);
            }

            $entityManager->persist($address);
            $entityManager->flush();

            return $this->redirectToRoute('user_profile');
        }

        return $this->render('address/new.html.twig', [
            'title' => 'Ajouter une adresse',
            'address' => $address,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/address/{id}/edit", name="address_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Address $address, AddressRepository $addressRepository): Response
    {
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        $entityManager = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {

            if ($address->getIsDefault() === true) {
                foreach ($addressRepository->findAll() as $key => $value) {
                    $value->setIsDefault(false);
                    $entityManager->persist($value);
                }
                $address->setIsDefault(true);
                $entityManager->persist($address);
            }

            $entityManager->flush();

            return $this->redirectToRoute('user_profile');
        }

        return $this->render('address/edit.html.twig', [
            'title' => 'Editer cette adresse',
            'address' => $address,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/address/{id}/delete", name="address_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Address $address): Response
    {
        if ($this->isCsrfTokenValid('delete'.$address->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($address);
            $entityManager->flush();
        }

        $this->addFlash('success', 'L\'adresse a bien été supprimée.');
        return $this->redirectToRoute('user_profile');
    }
}
