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
    			'q' 		=> $this->getParameter('search_text'),
    			'min_price' => $this->getParameter('min_price'),
    			'max_price' => $this->getParameter('max_price'),
    		],
    	]);

    	// Check status of request.
    	if (200 !== $response->getStatusCode()) {
    		// handle the HTTP request error (e.g. retry the request)
    	} else {
    		// Get the HTML contents of the page requested.
    		$content = $response->getContent();

    		// Send HTML to crawler.
    		$crawler = new Crawler($content);

    		// Obtain from all the HTML page only the ads prices.
    		$adsPricesElement = $crawler->filter('span[data-cy="adPrice"]');

    		// Add all available prices to a single array list.
    		$pricesList = [];
    		foreach ($adsPricesElement as $domElement) {
    			// Remove extra non-neded text from prices before adding to the price list.
    			$pricesList[] = str_replace(' cuc - ', '', $domElement->nodeValue);
    		}

    		// Calculate the average price.
			$averagePrice = array_sum($pricesList) / count($pricesList);
    	}

    	return $this->render('home/index.html.twig', [
    		'average_price' => number_format($averagePrice, 2, '.', '')
    	]);
    }
}
