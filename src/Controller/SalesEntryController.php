<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SalesEntryController extends AbstractController
{
    /**
     * @Route("/api/sales/entry", name="sales_entry")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SalesEntryController.php',
        ]);
    }
}
