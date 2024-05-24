<?php

namespace App\Controller;

use DateTime;
use Stripe\Stripe;
use App\Entity\User;
use App\Entity\Order;
use App\Services\Cart;
use App\Form\OrderType;
use App\Entity\OrderDetails;
use Stripe\Checkout\Session;
use App\Repository\ProductRepository;
use App\Services\StripeSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    #[Route('/compte/commande', name: 'order')]
    public function index(StripeSession $stripSession, Request $request,ProductRepository $repo,Cart $cart,EntityManagerInterface $manager): Response
    {

    if (!$this->getUser()->getAddresses()->getValues()){

        return $this->redirectToRoute('account_add_address');

    }


    $cart = $cart->get();
    $cartComplete=[];
    foreach ($cart as $id => $quantity) {
        $cartComplete[]=[
            'product'=> $repo->findOneById($id),
            'quantity'=>$quantity
        ];
    }


        $form = $this->createForm(OrderType::class,null,['user'=>$this->getUser()]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        $reference = uniqid();
          //  dd($form->getData());
          //  dd($form->get('addresses')->getData());
         // dd($form->get('transporteurs')->getData());
            $order = new Order();
            $order->setUser($this->getUser())
            -> setCarrier($form->get('transporteurs')->getData())
            ->setDelivery($form->get('addresses')->getData())
            ->setCreatedAt(new DateTime())
            ->setStatut(0)
            ->setReference($reference);

        $manager->persist($order);

        foreach ($cartComplete as $product) {
        
            $orderDetails = new OrderDetails();
            $orderDetails->setMyOrder($order)
           ->setProduct($product['product'])
           ->setQuantity($product['quantity'])
           ->setPrice($product['product']->getPrice());

           $manager ->persist($orderDetails);




           
        }


          $stripeUrlId= $stripSession->getStripeSession($cartComplete,$order);
          $order->setStripeSessionId($stripeUrlId[1]);
         // dd($checkout_session);

       $manager->flush();


        return $this->render('order/recap.html.twig', [
            'order' => $order,
            'cart'=>$cartComplete,
            'url_stripe'=>$stripeUrlId[0]
        ]);


        }


        return $this->render('order/order.html.twig', [
            'form'=>$form->createView(),
            'cart'=>$cartComplete
        ]);
    }
}
