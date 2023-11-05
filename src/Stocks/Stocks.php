<?php

namespace StocksChange;

use Exception;
use GuzzleHttp\Client;

class StockMarket
{
    private string $apiKeyStocks;
    private string $apiKeyNews;
    private string $apiKeyCurrency;
    private string $baseUrl;
    private $apiKey;
    private HttpClient $httpClient;

    public function __construct(array $config, $httpClient)
    {
        $this->apiKeyStocks = $config['api_keys']['stocks'];
        $this->apiKeyNews = $config['api_keys']['news'];
        $this->apiKeyCurrency = $config['api_keys']['currency'];
        $this->baseUrl = 'https://api.stockschange.com';
        $this->httpClient = $httpClient;
    }

    public function httpClient(string $method, string $endpoint, array $data = [])
    {
        $response = null;

        $url = "{$this->baseUrl}/{$endpoint}?api_key={$this->apiKey}";

        $client = new Client();

        if ($method === 'GET') {
            $response = $client->get($url);
        } elseif ($method === 'POST') {
            $response = $client->post($url, ['form_params' => $data]);
        }

        return $response->getBody()->getContents();
    }

    public function getChart(string $symbol, string $date, string $interval)
    {
        $endpoint = "v2/stocks/chart";
        $queryParams = [
            'ticker' => $symbol,
            'date' => $date,
            'interval' => $interval,
        ];
        return $this->makeApiRequest($endpoint, $queryParams);
    }

    public function getFinancials(string $symbol, string $baseCurrency = 'USD')
    {
        $endpoint = "v2/stocks/financials";
        $queryParams = [
            'ticker' => $symbol,
            'currency' => $baseCurrency,
        ];
        return $this->makeApiRequest($endpoint, $queryParams);
    }

    public function getList(string $stock_term, string $country = 'US', int $offset = 100)
    {
        $endpoint = "v2/stocks/list";
        $queryParams = [
            'term' => $stock_term,
            'country' => $country,
            'offset' => $offset,
        ];
        return $this->makeApiRequest($endpoint, $queryParams);
    }

    private function makeApiRequest(string $endpoint, array $queryParams)
    {
        try {
            $response = $this->httpClient->get($endpoint, $queryParams);

            if ($response) {
                $data = json_decode($response, true);

                if (is_array($data)) {
                    if (isset($data['error_code'])) {
                        throw new Exception("API Error: " . $data['error_message']);
                    } else {
                        return $data;
                    }
                } else {
                    throw new Exception("Invalid JSON Response: " . $response);
                }
            } else {
                throw new Exception("HTTP Request Error: No response received.");
            }
        } catch (Exception $e) {
            error_log("An error occurred: " . $e->getMessage());
            return ["error" => "An error occurred while fetching data."];
        }
    }

}
