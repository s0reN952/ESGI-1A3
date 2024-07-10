<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;

class NotificationController extends AbstractController
{
    #[Route('/notification', name: 'create_notification', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, LoggerInterface $logger): Response
    {
        $logger->info('Received request to create notification.');

        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['message']) || !isset($data['sujet'])) {
            $logger->error('Missing required fields: email, message, and subject are required.');
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Missing required fields: email, message, and subject are required.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $logger->error('Invalid email format.');
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid email format.'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $logger->info('Creating new notification.');

           
            $notification = new Notification();
            $notification->setEmailRecipient($data['email']);
            $notification->setMessage($data['message']);
            $notification->setSujet($data['sujet']);

            $entityManager->persist($notification);
            $entityManager->flush();

            $logger->info('Notification saved in database. Preparing email.');

          
            $email = (new Email())
                ->from('100.moonnaie@gmail.com') 
                ->to($notification->getEmailRecipient())
                ->subject($notification->getSujet())
                ->text($notification->getMessage());

            $logger->info('Sending email to ' . $notification->getEmailRecipient());

            $mailer->send($email);

            $logger->info('Email sent successfully.');

        
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Notification sent successfully'
            ], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            $logger->error('Failed to send email: ' . $e->getMessage());
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed to process notification: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
