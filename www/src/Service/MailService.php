<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Environment;

class MailService
{
    private $mailer;
    private $twig;

    public function __construct(\Swift_Mailer $mailer, Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendTokenEmail(string $mail, string $token, int $id)
    {
        $swiftMessage = (new \Swift_Message('Confirmation d\'inscription'))
            ->setFrom('contact@brewsymfo.fr')
            ->setTo($mail)
            ->setBody(
                $this->twig->render(
                    'email/confirm_account.html.twig',
                    ['token' => $token,
                    'id' => $id]
                )
            )
        ;
        $this->mailer->send($swiftMessage);

    }
}