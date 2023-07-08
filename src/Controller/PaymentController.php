<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Service\CartService;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

//gère les paiements et les redirections liées à Stripe

class PaymentController extends AbstractController
{
    private EntityManagerInterface $em;
    private UrlGeneratorInterface $generator;

    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $generator)
    {
        $this->em = $em;
        $this->generator = $generator;
    }

    #[Route('/order/create-session-stripe/{reference}', name: 'payment_stripe', methods:['POST'])]
    public function index($reference, CartService $cartService): RedirectResponse
    {
        $productStripe = [];
        $order = $this->em->getRepository(Order::class)->findOneBy(['reference' => $reference]);
        if (!$order) {
            return $this->redirectToRoute('cart_index');
        }

        // $cartItems = $cartService->getTotal();
        foreach ($order->getRecapDetails()->getValues() as $product) {
            $productData = $this->em->getRepository(Product::class)->findOneBy(['title'=> $product->getProduct()]);

            // $cartProduct = $cartItem['product'];
            $productStripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => number_format($productData->getPrice() * 100, 0, '', ''),

                    'product_data' => [
                        'name' => $productData->getTitle(),
                    ],
                ],
                'quantity' => $product->getQuantity(),
            ];
        }

       

        Stripe::setApiKey('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'); 

        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [$productStripe],
            'mode' => 'payment',
            'success_url' => $this->generator->generate('payment_success', [
                'reference' => $order->getReference(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generator->generate('payment_cancel', [
                'reference' => $order->getReference(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
        

        $order->setStripeSessionId($checkout_session->id);
        $this->em->flush();
        

        return new RedirectResponse($checkout_session->url);
    }

    #[Route('/order/success/{reference}', name: 'payment_success')]
    public function stripeSuccess($reference, CartService $cartService): Response
    {
        $cartService->removeCartAll();

        return $this->render('order/success.html.twig');
    }

    #[Route('/order/cancel/{reference}', name: 'payment_cancel')]
    public function stripeCancel($reference, CartService $cartService): Response
    {
        return $this->render('order/cancel.html.twig');
    }
}
