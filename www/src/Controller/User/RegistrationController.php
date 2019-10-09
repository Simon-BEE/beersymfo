<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\MailService;
use App\Service\TokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="security_register")
     */
    public function register(
        Request $request, 
        UserPasswordEncoderInterface $passwordEncoder,
        MailService $mail,
        TokenService $token): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            )
            ->setToken($token->generateToken());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            $mail->sendTokenEmail($user->getEmail(), $user->getToken(), $user->getId());
            $this->addFlash('success', 'Vous êtes bien enregistré, veuillez confirmer votre inscription depuis votre boîte mail.');

            return $this->redirectToRoute('security_login');
        }

        return $this->render('registration/register.html.twig', [
            'title' => 'Inscrivez-vous',
            'registrationForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/confirm", name="security_confirm")
     */
    public function confirmAccount(UserRepository $userRepo, Request $request)
    {
        if ($request->get('t') && $request->get('i')) {
            $getId = $request->get('i');
            $getToken = $request->get('t');

            if ($userRepo->find($getId) === $userRepo->findOneBy(['token' => $getToken])) {
                $user = $userRepo->find($getId);
                $user->setToken('');
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre compte a bien été validé, vous pouvez vous connecter.');
            }else{
                $this->addFlash('error', 'Une erreur est survenue, veuillez recliquer sur le lien envoyé');
            }
        }else{
            $this->addFlash('error', 'Une erreur est survenue, veuillez contacter un administrateur');
        }

        return $this->redirectToRoute('security_login');
    }
}
