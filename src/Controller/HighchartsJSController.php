<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Ob\HighchartsBundle\Highcharts\Highstock;

class HighchartsJSController extends AbstractController
{
    /**
     * @Route("/history", name="history")
     */
    public function index(): Response
    {

        $data = [
            [
                1574173800000,
                66.57,
            ],
            [
                1574260200000,
                65.8,
            ],
            [
                1574346600000,
                65.5,
            ],
            [
                1574433000000,
                65.44,
            ],
            [
                1574692200000,
                66.59,
            ],
            [
                1574778600000,
                66.07,
            ],
            [
                1574865000000,
                66.96,
            ],
            [
                1575037800000,
                66.81,
            ],
        ];

        // Chart
        $series = [
            [
                'name' => 'USD',
                'data' => $data,
            ],
        ];

        $ob = new Highstock();
        $ob->chart->renderTo('pricehistorychart');  // The #id of the div where to render the chart
        $ob->title->text('USD Price');
        // $ob->rangeSelector->selected(true);
        // $ob->xAxis->title(array('text'  => "Horizontal axis title"));
        $ob->yAxis->title(['text'  => 'CUP']);
        $ob->series($series);
        $ob->credits->enabled(false);
        // $ob->tooltip->valueDecimals(2);

        return $this->render('highcharts_js/index.html.twig', [
            'controller_name' => 'HighchartsJSController',
            'chart' => $ob,
        ]);
    }
}
