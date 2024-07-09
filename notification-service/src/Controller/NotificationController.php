<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;  // Ajouter l'interface Logger
use App\Entity\Notification;  // Importer correctement la classe Notification

class NotificationController extends AbstractController
{
    #[Route('/notification', name: 'app_notification', methods: ['POST'])]
    public function sendNotification(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, LoggerInterface $logger): Response
    {
        $data = json_decode($request->getContent(), true);

        // Validation des données d'entrée
        if (!isset($data['email']) || !isset($data['message']) || !isset($data['sujet'])) {
            $logger->error('Missing required fields');
            return $this->json([
                'status' => 'error',
                'message' => 'Missing required fields: email, message, and subject are required.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $logger->error('Invalid email format');
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid email format.'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $logger->info('Creating notification entity');

            // Créer une nouvelle notification
            $notification = new Notification();
            $notification->setEmailRecipient($data['email']);
            $notification->setMessage($data['message']);
            $notification->setSujet($data['sujet']);

            // Persister l'entité
            $entityManager->persist($notification);
            $entityManager->flush();

            $logger->info('Preparing email');

            // Préparer l'email à envoyer
            $email = (new Email())
                ->from('100.moonnaie@gmail.com') // Remplacez par votre adresse email d'expéditeur
                ->to($notification->getEmailRecipient())
                ->subject($notification->getSujet())
                ->text($notification->getMessage());

            $logger->info('Sending email to ' . $notification->getEmailRecipient());

            // Envoyer l'email
            $mailer->send($email);

            $logger->info('Email sent successfully');

            // Retourner une réponse JSON de succès
            return $this->json([
                'status' => 'success',
                'message' => 'Notification sent successfully'
            ]);
        } catch (\Exception $e) {
            $logger->error('Failed to send email: ' . $e->getMessage());
            return $this->json([
                'status' => 'error',
                'message' => 'Failed to process notification: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
