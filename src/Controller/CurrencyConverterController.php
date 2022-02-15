<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PriceHistory;
use App\Util\UtilityBox;

class CurrencyConverterController extends AbstractController
{
    /**
     * @Route("/convert", name="currency_converter")
     */
    public function index(EntityManagerInterface $em): Response
    {

        $priceHistoryRepository = $em->getRepository(PriceHistory::class);

        // Get last price logged.
        $lastPrice = $priceHistoryRepository->findLastPriceInserted();
        $averagePrice = $lastPrice->getClosingPrice();

        // Calculate a max price value.
        $calcMaxPrice = UtilityBox::generateMaxPrice($averagePrice);

        return $this->render(
            'currency_converter/index.html.twig', [
            'calc_max_price' => $calcMaxPrice,
            ]
        );
    }
}
