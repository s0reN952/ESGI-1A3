<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\Commande;

class CommandeController extends AbstractController
{
    public function list(): Response
    {
        $commandes = $this->getDoctrine()->getRepository(Commande::class)->findAll();
        return $this->json($commandes, 200, [], ['groups' => 'commande']);
    }

    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $commande = new Commande();
        $commande->setProductId($data['product_id']);
        $commande->setCustomerEmail($data['customer_email']);
        $commande->setQuantity($data['quantity']);
        $commande->setTotalPrice($data['total_price']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($commande);
        $entityManager->flush();

        return $this->json($commande, 201, [], ['groups' => 'commande']);
    }

    public function update($id, Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $commande = $entityManager->getRepository(Commande::class)->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Aucune commande trouvée pour l\'id '.$id);
        }

        $data = json_decode($request->getContent(), true);

        $commande->setProductId($data['product_id']);
        $commande->setCustomerEmail($data['customer_email']);
        $commande->setQuantity($data['quantity']);
        $commande->setTotalPrice($data['total_price']);

        $entityManager->flush();

        return $this->json($commande, 200, [], ['groups' => 'commande']);
    }
    
    public function delete($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $commande = $entityManager->getRepository(Commande::class)->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Aucune commande trouvée pour l\'id '.$id);
        }

        $entityManager->remove($commande);
        $entityManager->flush();

        return new Response(null, 204);
    }

    #[Route('/submit-order', name: 'submit_order', methods: ['POST'])]
    public function submitOrder(Request $request, EntityManagerInterface $entityManager, HttpClientInterface $httpClient): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $order = new Commande();
        $order->setCustomerEmail($data['customer_email']);
        $order->setTotalPrice($data['amount']);  // Assurez-vous que les champs correspondent à votre entité Commande
        $entityManager->persist($order);
        $entityManager->flush();

        $billingData = [
            'amount' => $order->getTotalPrice(),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'customer_email' => $order->getCustomerEmail()
        ];

        $response = $httpClient->request('POST', 'http://billing-service.local/create-invoice', [
            'json' => $billingData
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to create invoice');
        }

        return new JsonResponse(['status' => 'Order processed and invoice created']);
    }
}
