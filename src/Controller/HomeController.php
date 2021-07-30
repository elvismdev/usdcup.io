<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
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
        if (isset($response[0])) {
            $response = $response[0];
        }

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
            // If we have ads data, iterate through the list and calculate average price.
            $adsList = [];
            if (isset($response['data']['adsPerPage']['edges']) && !empty($response['data']['adsPerPage']['edges'])) {
                $adsList = $response['data']['adsPerPage']['edges'];
            }

            // Add all available prices to a single array list.
            $pricesList = [];
            foreach ($adsList as $ad) {
                // Continue to next element if Ad is NOT set in CUP, or listed price is above of our set max price limit.
                if ($ad['node']['currency'] !== 'CUP'
                    || $ad['node']['price'] > $this->getParameter('max_price')
                ) {
                    continue;
                }

                // Set ad price to the prices list.
                $pricesList[] = $ad['node']['price'];
            }

            dump($pricesList);

            // Set the total prices collected.
            $pricesQty = count($pricesList);

            // Calculate the average price.
            $averagePrice = array_sum($pricesList) / $pricesQty;

            // Set the JSON response.
            $jsonResponse = $this->json(
                [
                'success'               => true,
                'remote_status_code'    => $response['status'],
                'average_price'         => (float) number_format($averagePrice, 2, '.', ''),
                'total_ads_evaluated'   => $pricesQty,
                ]
            );
        }

        return $jsonResponse;
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
