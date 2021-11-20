<?php

namespace App\Service;

use App\Util\UtilityBox;

/**
 * Service to handle Revolico GraphQL API calls.
 */
class RevolicoService
{

    /**
     * @var array
     */
    protected $bannedWords;

    /**
     * @var string
     */
    protected $searchQuery;

    /**
     * @var string
     */
    protected $priceGte;

    /**
     * @var string
     */
    protected $priceLte;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @var array
     */
    protected $adsList = [];

    public function __construct(
        array $bannedWords,
        string $searchText,
        string $priceGte,
        string $priceLte,
        string $endpoint,
        string $userAgent
    ) {
        // Add ! prefix to banned words for searching.
        $bannedWords = UtilityBox::addExclPrefix($bannedWords);
        $this->bannedWords = $bannedWords;

        // Create string with banned words.
        $bannedWordsStr = implode(' ', $bannedWords);

        // Create the full power keyword search text.
        $this->searchQuery = '"'.$searchText.'" '.$bannedWordsStr;

        // Set min and max values.
        $this->priceGte = $priceGte;
        $this->priceLte = $priceLte;

        // Set endpoint.
        $this->endpoint = $endpoint;

        // Set userAgent.
        $this->userAgent = $userAgent;
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
                CURLOPT_URL => $this->endpoint,
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
                'User-Agent: '.$this->userAgent,
                ),
            ]
        );

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
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


    public function getAds()
    {
        // Prepare variables for GraphQL query.
        $variables = [
          'subcategorySlug' => 'compra-venta_divisas',
          'contains' => $this->searchQuery,
          'priceGte' => $this->priceGte,
          'priceLte' => $this->priceLte,
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

        // Set ads if found in adsList property.
        if (isset($response['data']['adsPerPage']['edges']) && !empty($response['data']['adsPerPage']['edges'])) {
            $this->adsList = $response['data']['adsPerPage']['edges'];
        }

        return $response;
    }

    public function findAveragePrice()
    {
        // Add all available prices to a single array list.
        $pricesList = [];
        foreach ($this->adsList as $ad) {
            // Continue to next element if Ad is NOT set in CUP, or listed price is above of our set max price limit.
            if ($ad['node']['currency'] !== 'CUP'
                || $ad['node']['price'] > $this->priceLte
            ) {
                continue;
            }

            // Set ad price to the prices list.
            $pricesList[] = $ad['node']['price'];
        }

        // Set the total prices collected.
        $pricesQty = count($pricesList);

        // Calculate the average price.
        $averagePrice = array_sum($pricesList) / $pricesQty;

        return [
          'pricesQty' => $pricesQty,
          'averagePrice' => $averagePrice,
        ];
    }
}
