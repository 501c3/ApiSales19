<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SalesXtraController extends AbstractController
{
    /**
     * @Route("/api/sales/xtra", name="sales_xtra")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SalesXtraController.php',
        ]);
    }
}
