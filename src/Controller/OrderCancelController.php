<?php

namespace App\Controller;

use App\Entity\Order;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderCancelController extends AbstractController
{
    #[Route('/compte/commande/erreur/{stripeSessionId}', name: 'order_cancel')]
    public function index(Order $order): Response
    {
        return $this->render('order_cancel/index.html.twig', [
            'order'=>$order
        ]);
    }
}
