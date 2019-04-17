<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SalesFinishController extends AbstractController
{
    /**
     * @Route("/api/sales/finish", name="sales_finish")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SalesFinishController.php',
        ]);
    }
}
