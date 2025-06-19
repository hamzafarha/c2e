<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortiestockController extends AbstractController
{
    #[Route('/sortiestock', name: 'app_sortiestock')]
    public function index(): Response
    {
        return $this->render('sortiestock/index.html.twig', [
            'controller_name' => 'SortiestockController',
        ]);
    }
}
