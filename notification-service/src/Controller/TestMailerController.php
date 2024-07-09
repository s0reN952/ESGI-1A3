<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TestMailerController extends AbstractController
{
    #[Route('/test-mailer', name: 'test_mailer')]
    public function index(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('100.moonnaie@gmail.com')  // Remplacez par votre adresse email d'expéditeur
            ->to('ilyas.salek@gmail.com') // Remplacez par l'adresse email du destinataire
            ->subject('Test from Symfony Mailer!')
            ->text('This is a test email sent from Symfony.');

        $mailer->send($email);

        return new Response('Email sent successfully!');
    }
}
