<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\RevolicoService;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig');
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
