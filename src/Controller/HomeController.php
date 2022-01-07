<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\RevolicoService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PriceHistory;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(EntityManagerInterface $em)
    {
        // Get last price logged.
        $priceHistoryRepository = $em->getRepository(PriceHistory::class);
        $lastPrice = $priceHistoryRepository->findLastPriceInserted();
        $averagePrice = $lastPrice->getClosingPrice();

        // Get yesterday's price.
        $yesterdayPrice = $priceHistoryRepository->findYesterdayPrice($lastPrice->getCreatedAt());

        // Set amount and percent change vars.
        $amountChange = 0;
        $percentChange = 0;
        $faCaret = 'down';

        // If we have an average price and the yesterday's price, calculate the difference for printing.
        if ($averagePrice && $yesterdayPrice) {
            // Get yesterday closing price.
            $yesterdayPrice = $yesterdayPrice->getClosingPrice();

            // Calculate price change difference.
            $amountChange = $yesterdayPrice - $averagePrice;

            // Calculate percentage change difference.
            $percentChange = ($amountChange / $yesterdayPrice) * 100;

            // Set tweet triangle icon if the value is an increase or decrease from yesterday.
            if ($amountChange < 0) {
                $faCaret = 'up';
            }
        }

        return $this->render(
            'home/index.html.twig',
            [
                'average_price' => $averagePrice,
                'total_ads_evaluated' => $lastPrice->getAdsPricesEval(),
                'amount_change' => round(abs($amountChange), 2),
                'percent_change' => round(abs($percentChange), 2),
                'max_price' => 70,
                'min_price' => 64,
                'max_price_ad_url' => 'https://www.google.com',
                'min_price_ad_url' => 'https://www.google.com',
                'fa_caret' => $faCaret,
            ]
        );
    }


    /**
     * @Route("/api/get_average_price", name="get_average_price", methods={"GET"}, defaults={"_format": "json"})
     */
    public function getAveragePrice()
    {

        // Initialize platform service.
        $revolicoService = new RevolicoService(
            $this->getParameter('banned_words'),
            $this->getParameter('search_text'),
            $this->getParameter('min_price'),
            $this->getParameter('max_price'),
            $this->getParameter('ad_platform_graphql_endpoint'),
            $this->getParameter('user_agent')
        );

        // Get a reponse from platform.
        $response = $revolicoService->getAds();

        // Check status of request.
        if (isset($response['errors']) && !empty($response['errors'])) {
            // Return success "false" response so we capture and handle from frontend JS.
            $jsonResponse = $this->json(
                [
                'success'               => false,
                'remote_status_code'    => $response['status'],
                'remote_errors'         => $response['errors'],
                'average_price'         => null,
                ]
            );
        } else {
            // Find the average price.
            $averagePriceResults = $revolicoService->findAveragePrice();

            // Set the JSON response.
            $jsonResponse = $this->json(
                [
                'success'               => true,
                'remote_status_code'    => $response['status'],
                'average_price'         => (float) number_format($averagePriceResults['averagePrice'], 2, '.', ''),
                'total_ads_evaluated'   => $averagePriceResults['pricesQty'],
                ]
            );
        }

        return $jsonResponse;
    }


    /**
     * Custom stripos() function to find multiple needles in one haystack.
     * @param string $haystack
     * @param array  $needle
     * @param bool   $offset
     *
     * @return bool
     */
    private function _striposa($haystack, $needle, $offset = 0)
    {
        if (!is_array($needle)) {
            $needle = array($needle);
        }
        foreach ($needle as $query) {
            if (stripos($haystack, $query, $offset) !== false) {
                return true;
            }
        }

        return false;
    }
}
