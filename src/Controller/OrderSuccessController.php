<?php

namespace App\Controller;

use App\Entity\Order;
use Stripe\StripeClient;
use App\Repository\OrderRepository;
use App\Services\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderSuccessController extends AbstractController
{
    #[Route('/compte/commande/merci/{stripeSessionId}', name: 'order_success')]
    public function index(Order $order,$stripeSessionId,Cart $cart,EntityManagerInterface $manager): Response
    {

        
       // dd($CHECKOUT_SESSION_ID);
        $stripeSecretKey = $this->getParameter('STRIPE_KEY');
        $stripe = new StripeClient($stripeSecretKey);

        $session = $stripe->checkout->sessions->retrieve($stripeSessionId);
       // $customer = $stripe->customers->retrieve($session->customer);

       //on vide la panier
       $cart->remove();

       //on met la commande à payée
       $order->setStatut(1);

       $manager->flush();


       
        return $this->render('order_success/index.html.twig', [
          'total'=>$session->amount_total,
          'order'=>$order
        ]);
    }
}
