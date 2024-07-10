<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class TestMailerController extends AbstractController
{
    #[Route('/test-mailer', name: 'test_mailer')]
    public function index(MailerInterface $mailer, LoggerInterface $logger): Response
    {
        $logger->info('Preparing email');

        $email = (new Email())
            ->from('100.moonnaie@gmail.com')  
            ->to('ilyas.salek@gmail.com') 
            ->subject('Test from Symfony Mailer!')
            ->text('This is a test email sent from Symfony.');

        try {
            $mailer->send($email);
            $logger->info('Email sent successfully');
            return new Response('Email sent successfully!');
        } catch (\Exception $e) {
            $logger->error('Failed to send email: ' . $e->getMessage());
            return new Response('Failed to send email: ' . $e->getMessage(), 500);
        }
    }
}
