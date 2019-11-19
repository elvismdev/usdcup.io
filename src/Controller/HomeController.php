<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

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
        // Initialize HTTP client.
        $client = HttpClient::create();

        // Make an HTTP GET request to https://www.revolico.com/compra-venta/divisas/search.html?q=...&min_price=...&max_price=...
        $response = $client->request('GET', $this->getParameter('search_page_url'), [
            // Set request headers.
            'headers' => [
                'User-Agent' => $this->getParameter('user_agent')
            ],

            // Set search parameters. These values are automatically encoded before including them in the URL
            'query' => [
                'q'         => $this->getParameter('search_text'),
                'min_price' => $this->getParameter('min_price'),
                'max_price' => $this->getParameter('max_price'),
            ],
        ]);

        // Get the status code.
        $statusCode = $response->getStatusCode();

        // Check status of request.
        if (200 !== $statusCode) {
            // Return success "false" response so we capture and handle from frontend JS.
            return $this->json([
                'success'               => false,
                'remote_status_code'    => $statusCode,
                'average_price'         => null
            ]);

        } else {
            // Get the HTML contents of the page requested.
            $content = $response->getContent();

            // Send HTML to crawler.
            $crawler = new Crawler($content);

            // Get banned words from settings.
            $bannedWords = $this->getParameter('banned_words');

            // Obtain all the ads <li> rows.
            $adRowElements = $crawler
            ->filter('li[data-cy="adRow"]')
            // Exclude Ads with banned words in the title.
            ->reduce(function (Crawler $node, $i) use ($bannedWords) {
                // Get the adTitle element.
                $adTitleElement = $node->filter('span[data-cy="adTitle"]');

                // Check if Ad title contains banned words.
                if ($this->striposa($adTitleElement->html(), $bannedWords) === false) {
                    // Include this Ad, it seems to NOT have banned word in his title.
                    return true;
                } else {
                    // Do not include this Ad, it seems it has a banned word in his title.
                    return false;
                }
            });

            // Get the ads prices elements.
            $adsPricesElement = $adRowElements->filter('span[data-cy="adPrice"]');

            // Add all available prices to a single array list.
            $pricesList = [];
            foreach ($adsPricesElement as $domElement) {
                // Remove extra non-neded text from prices before adding to the price list.
                $pricesList[] = str_replace(' cuc - ', '', $domElement->nodeValue);
            }

            // Set the total prices collected.
            $pricesQty = count($pricesList);

            // Calculate the average price.
            $averagePrice = array_sum($pricesList) / $pricesQty;

            return $this->json([
                'success'               => true,
                'remote_status_code'    => $statusCode,
                'average_price'         => (float) number_format($averagePrice, 2, '.', ''),
                'total_ads_evaluated'   => $pricesQty
            ]);
        }
    }


    /**
     * Custom stripos() function to find multiple needles in one haystack.
     * @param string $haystack
     * @param array $needle
     * @param bool $offset
     * @return bool
     */
    private function striposa($haystack, $needle, $offset=0) {
        if(!is_array($needle)) $needle = array($needle);
        foreach($needle as $query) {
            if(stripos($haystack, $query, $offset) !== false) return true;
        }
        return false;
    }
}
