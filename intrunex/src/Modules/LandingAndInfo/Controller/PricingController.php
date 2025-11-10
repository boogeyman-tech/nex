<?php

namespace App\Modules\LandingAndInfo\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PricingController extends AbstractController
{
    #[Route('/pricing', name: 'pricing_page')]
    public function index(): Response
    {
        return $this->render('@LandingAndInfo/pricing/index.html.twig', [
            'controller_name' => 'PricingController',
        ]);
    }
}
