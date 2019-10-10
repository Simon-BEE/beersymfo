<?php

namespace App\Controller\User;

use App\Form\UserType;
use App\Repository\AddressRepository;
use App\Repository\CommandRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/profile", name="user_profile")
     */
    public function profile(AddressRepository $addressRepository, CommandRepository $commandRepository)
    {
        if ($this->getUser()->getToken() !== '') {
            $this->addFlash('error', 'Veillez à bien confirmer votre inscription avant de vouloir vous connecter');
            return $this->redirectToRoute('security_logout');
        }

        //dd($addressRepository->findBy(['user' => $this->getUser()->getId()]));
        $commands = $commandRepository->findBy(['user' => $this->getUser()->getId()], ['id' => 'desc'], 3);

        return $this->render('user/profile.html.twig', [
            'title' => 'Votre profil',
            'addresses' => $addressRepository->findBy(['user' => $this->getUser()->getId()]),
            'commands' => $commands
        ]);
    }

    /**
     * @Route("/profile/update", name="user_update")
     */
    public function update(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        $oldPwd = $user->getPassword();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (password_verify($user->getOldPassword(), $oldPwd)) {
                $user->setPassword($encoder->encodePassword($user, $user->getPassword()));

                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'Vos données ont bien été modifiées.');
            }else{
                $this->addFlash('error', 'Votre ancien mot de passe est incorrect.');
            }
        }
        
        return $this->render('user/update.html.twig', [
            'title' => 'Modifier votre profil',
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
    
}
