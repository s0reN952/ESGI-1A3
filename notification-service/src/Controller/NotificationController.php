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
use Symfony\Contracts\HttpClient\HttpClientInterface; // Ajouté pour l'utilisation de HttpClientInterface

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

    #[Route('/submit-order-notification', name: 'submit_order_notification', methods: ['POST'])]
    public function submitOrderNotification(Request $request, EntityManagerInterface $entityManager, HttpClientInterface $httpClient, LoggerInterface $logger): JsonResponse
    {
        $logger->info('Received request to submit order notification.');

        $data = json_decode($request->getContent(), true);

        if (!isset($data['customer_email']) || !isset($data['amount'])) {
            $logger->error('Missing required fields: customer_email and amount are required.');
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Missing required fields: customer_email and amount are required.'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $logger->info('Creating new order notification.');

            // Suppose you have an OrderNotification entity or you can use Notification entity
            $orderNotification = new Notification();
            $orderNotification->setEmailRecipient($data['customer_email']);
            $orderNotification->setMessage('Your order has been received.');
            $orderNotification->setSujet('Order Confirmation');

            $entityManager->persist($orderNotification);
            $entityManager->flush();

            $logger->info('Order notification saved in database. Preparing HTTP request to billing service.');

            // Prepare data for billing service
            $billingData = [
                'amount' => $data['amount'],
                'due_date' => date('Y-m-d', strtotime('+30 days')), // Example due date
                'customer_email' => $data['customer_email']
            ];

            // Send HTTP request to billing service
            $response = $httpClient->request('POST', 'http://billing-service.local/create-invoice', [
                'json' => $billingData
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to create invoice');
            }

            $logger->info('HTTP request to billing service successful.');

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Order notification processed and invoice created'
            ], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            $logger->error('Failed to process order notification: ' . $e->getMessage());
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed to process order notification: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
