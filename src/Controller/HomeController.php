<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DomCrawler\Crawler;
use App\Util\UtilityBox;

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
     * Definition of the GraphQL query.
     *
     * @return string
     */
    private function _getGraphQLJSONStringQuery()
    {
        $query = 'query AdsSearch($category: ID, $subcategory: ID, $contains: String, $priceGte: Float, $priceLte: Float, $sort: [adsPerPageSort], $hasImage: Boolean, $categorySlug: String, $subcategorySlug: String, $page: Int, $provinceSlug: String, $municipalitySlug: String, $pageLength: Int) {\\n  adsPerPage(category: $category, subcategory: $subcategory, contains: $contains, priceGte: $priceGte, priceLte: $priceLte, hasImage: $hasImage, sort: $sort, categorySlug: $categorySlug, subcategorySlug: $subcategorySlug, page: $page, provinceSlug: $provinceSlug, municipalitySlug: $municipalitySlug, pageLength: $pageLength) {\\n    pageInfo {\\n      ...PaginatorPageInfo\\n      __typename\\n    }\\n    edges {\\n      node {\\n        id\\n        title\\n        price\\n        currency\\n        shortDescription\\n        permalink\\n        imagesCount\\n        updatedOnToOrder\\n        isAuto\\n        province {\\n          id\\n          name\\n          slug\\n          __typename\\n        }\\n        municipality {\\n          id\\n          name\\n          slug\\n          __typename\\n        }\\n        __typename\\n      }\\n      __typename\\n    }\\n    meta {\\n      total\\n      __typename\\n    }\\n    __typename\\n  }\\n}\\n\\nfragment PaginatorPageInfo on CustomPageInfo {\\n  startCursor\\n  endCursor\\n  hasNextPage\\n  hasPreviousPage\\n  pageCount\\n  __typename\\n}\\n';

        return $query;
    }


    /**
     * Excecute GraphQL query.
     *
     * @param string $query
     * @param array  $variables
     *
     * @return string
     */
    private function _graphQLExecQuery(string $query, array $variables = [])
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $this->getParameter('ad_platform_graphql_endpoint'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '[
                    {
                        "variables": '.json_encode($variables).',
                        "query": "'.$query.'"
                    }
                ]',
                CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'User-Agent: '.$this->getParameter('user_agent'),
                ),
            ]
        );

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }


    /**
     * @Route("/api/get_average_price", name="get_average_price", methods={"GET"}, defaults={"_format": "json"})
     */
    public function getAveragePrice()
    {
        // Get banned words from settings.
        $bannedWords = $this->getParameter('banned_words');
        $bannedWords = UtilityBox::addExclPrefix($bannedWords);

        // Create string with banned words.
        $bannedWordsStr = implode(' ', $bannedWords);

        // Create the full power keyword search text.
        $searchQuery = '"'.$this->getParameter('search_text').'" '.$bannedWordsStr;

        // Prepare variables for GraphQL query.
        $variables = [
            'subcategorySlug' => 'compra-venta_divisas',
            'contains' => $searchQuery,
            'priceGte' => $this->getParameter('min_price'),
            'priceLte' => $this->getParameter('max_price'),
            'sort' => [
                [
                'order' => 'desc',
                'field' => 'relevance',
                ],
            ],
            'page' => 1,
            'pageLength' => 100,
        ];
        // Fire the GraphQL query and retrieve data from ad platform.
        $response = $this->_graphQLExecQuery(
            $this->_getGraphQLJSONStringQuery(),
            $variables
        );

        // Convert response from JSON to array.
        $response = json_decode($response, true);
        // Return the first element of the array response.
        $response = current($response);


        // dump($response);
        // die;

        // Get the status code.
        $statusCode = $response->getStatusCode();

        // Check status of request.
        if (200 !== $statusCode) {
            // Return success "false" response so we capture and handle from frontend JS.
            return $this->json([
                'success'               => false,
                'remote_status_code'    => $statusCode,
                'average_price'         => null,
            ]);
        } else {
            // Get the HTML contents of the page requested.
            $content = $response->getContent();

            // Send HTML to crawler.
            $crawler = new Crawler($content);

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
            })
            ;

            // Get the ads prices elements.
            $adsPricesElement = $adRowElements->filter('span[data-cy="adPrice"]');

            // Add all available prices to a single array list.
            $pricesList = [];
            foreach ($adsPricesElement as $domElement) {
                // Continue to next element if Ad price is set in CUP.
                if (strpos($domElement->nodeValue, 'CUP') !== false) {
                    continue;
                }

                // Remove extra non-neded text from prices before adding to the price list.
                $pricesList[] = str_replace([' cuc - ', ' usd - '], '', $domElement->nodeValue);
            }

            // Set the total prices collected.
            $pricesQty = count($pricesList);

            // Calculate the average price.
            $averagePrice = array_sum($pricesList) / $pricesQty;

            return $this->json([
                'success'               => true,
                'remote_status_code'    => $statusCode,
                'average_price'         => (float) number_format($averagePrice, 2, '.', ''),
                'total_ads_evaluated'   => $pricesQty,
            ]);
        }
    }


        /**
        * Custom stripos() function to find multiple needles in one haystack.
        * @param string $haystack
        * @param array  $needle
        * @param bool   $offset
        *
        * @return bool
        */
    private function striposa($haystack, $needle, $offset = 0)
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
