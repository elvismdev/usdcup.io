<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HighchartsJSController extends AbstractController
{
    /**
     * @Route("/history", name="history")
     */
    public function index(): Response
    {
        return $this->render('highcharts_js/index.html.twig', [
            'controller_name' => 'HighchartsJSController',
        ]);
    }
}
