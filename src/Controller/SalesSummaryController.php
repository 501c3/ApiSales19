<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SalesSummaryController extends AbstractController
{
    /**
     * @Route("/api/sales/summary", name="sales_summary")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SalesSummaryController.php',
        ]);
    }
}
