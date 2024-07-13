<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Facture;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FactureController extends AbstractController
{
    #[Route('/facture', name: 'app_facture')]
    public function index(): Response
    {
        return $this->render('facture/index.html.twig', [
            'controller_name' => 'FactureController',
        ]);
    }

    #[Route('/facture/create', name: 'facture_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $facture = new Facture();
        $facture->setAmount($data['amount']);
        $facture->setDueDate(new \DateTime($data['due_date']));
        $facture->setCustomerEmail($data['customer_email']);

        $entityManager->persist($facture);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Facture created'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/submit-order', name: 'submit_order', methods: ['POST'])]
    public function submitOrder(Request $request, EntityManagerInterface $entityManager, HttpClientInterface $httpClient): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Création de l'entité Facture et sauvegarde dans la DB
        $facture = new Facture();
        $facture->setAmount($data['amount']);
        $facture->setDueDate(new \DateTime('+30 days')); // Exemple de date d'échéance
        $facture->setCustomerEmail($data['customer_email']);
        
        $entityManager->persist($facture);
        $entityManager->flush();

        // Préparer les données pour Billing Service
        $billingData = [
            'amount' => $facture->getAmount(),
            'due_date' => $facture->getDueDate()->format('Y-m-d'),
            'customer_email' => $facture->getCustomerEmail()
        ];

        // Envoi à Billing Service
        $response = $httpClient->request('POST', 'http://billing-service.local/create-invoice', [
            'json' => $billingData
        ]);

        // Gérer la réponse de Billing Service
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to create invoice');
        }

        return new JsonResponse(['status' => 'Order processed and invoice created']);
    }
}
