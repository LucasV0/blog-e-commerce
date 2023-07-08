<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\RecapDetails;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//gère la création et la préparation des commandes pour les utilisateurs connectés

class OrderController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/order/create', name: 'order_create')]
    public function index(CartService $cartService): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
            'recapCart' => $cartService->getTotal()
        ]);
    }

    #[Route('/order/verify', name: 'order_prepare', methods:['POST'])]
    public function prepareOrder(CartService $cartService): Response 
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $datetime = new \DateTimeImmutable('now');
        $order = new Order();
        $reference = $datetime->format('dmY'). '-' . uniqid();
        $order->setUser($this->getUser());
        $order->setReference($reference);
        $order->setCreatedAt($datetime);
        $order->setIsPaid(0);
        $order->setMethod('stripe');
        $this->em->persist($order);
        foreach ($cartService->getTotal() as $product) 
        {
            $recapDetails = new RecapDetails();
            $recapDetails->setOrderProduct($order);
            $recapDetails->setQuantity($product['quantity']);
            $recapDetails->setPrice($product['product']->getPrice());
            $recapDetails->setProduct($product['product']->getTitle());
            $recapDetails->setTotalRecap($product['product']->getPrice() * $product['quantity']);
            $this->em->persist($recapDetails);
        }
        $this->em->flush();
        return $this->render('order/recap.html.twig', [
            'method' => $order->getMethod(),
            'recapCart' => $cartService->getTotal(),
            'reference' => $order->getReference()
        ]);
    }
}
