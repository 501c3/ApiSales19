<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SalesParticipantController extends AbstractController
{
    /**
     * @Route("/api/sales/participant", name="sales_participant")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SalesParticipantController.php',
        ]);
    }
}
