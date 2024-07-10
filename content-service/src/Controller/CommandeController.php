<?php
// src/Controller/CommandeController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Commande;

class CommandeController extends AbstractController
{
    /**
     * @Route("/commandes", name="commande_list", methods={"GET"})
     */
    public function list(): Response
    {
        $commandes = $this->getDoctrine()->getRepository(Commande::class)->findAll();
        
        // Serializer for transforming entities to JSON
        $response = $this->json($commandes, 200, [], ['groups' => 'commande']);
        return $response;
    }

    /**
     * @Route("/commandes", name="commande_create", methods={"POST"})
     */
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

    /**
     * @Route("/commandes/{id}", name="commande_update", methods={"PUT"})
     */
    public function update($id, Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $commande = $entityManager->getRepository(Commande::class)->find($id);

        if (!$commande) {
            throw $this->createNotFoundException(
                'Aucune commande trouvée pour l\'id '.$id
            );
        }

        $data = json_decode($request->getContent(), true);

        $commande->setProductId($data['product_id']);
        $commande->setCustomerEmail($data['customer_email']);
        $commande->setQuantity($data['quantity']);
        $commande->setTotalPrice($data['total_price']);

        $entityManager->flush();

        return $this->json($commande, 200, [], ['groups' => 'commande']);
    }

    /**
     * @Route("/commandes/{id}", name="commande_delete", methods={"DELETE"})
     */
    public function delete($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $commande = $entityManager->getRepository(Commande::class)->find($id);

        if (!$commande) {
            throw $this->createNotFoundException(
                'Aucune commande trouvée pour l\'id '.$id
            );
        }

        $entityManager->remove($commande);
        $entityManager->flush();

        return new Response(null, 204);
    }
}
