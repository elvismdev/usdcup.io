<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Ob\HighchartsBundle\Highcharts\Highstock;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PriceHistory;

class HighchartsJSController extends AbstractController
{
    /**
     * @Route("/history", name="history")
     */
    public function index(EntityManagerInterface $em): Response
    {

        $priceHistoryRepository = $em->getRepository(PriceHistory::class);
        $allPriceHistory = $priceHistoryRepository->findAllAsArray();

        $data = [];
        foreach ($allPriceHistory as $key => $value) {
            $data[] = [
                (int) $value['unixCreatedAt'],
                (float) $value['closingPrice'],
            ];
        }
        
        // Chart
        $series = [
            [
                'name' => 'USD',
                'data' => $data,
            ],
        ];

        $ob = new Highstock();
        $ob->chart->renderTo('pricehistorychart');  // The #id of the div where to render the chart
        $ob->title->text('Precio X $1 USD');
        // $ob->rangeSelector->selected(true);
        // $ob->xAxis->title(array('text'  => "Horizontal axis title"));
        $ob->yAxis->title(['text'  => 'CUP']);
        $ob->series($series);
        $ob->credits->enabled(false);
        // $ob->tooltip->valueDecimals(2);

        return $this->render('highcharts_js/index.html.twig', [
            'chart' => $ob,
        ]);
    }
}
