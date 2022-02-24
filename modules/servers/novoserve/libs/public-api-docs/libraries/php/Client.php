<?php

namespace NovoServe\API;

class Client
{
    private string $apiUrl = 'https://api.novoserve.com/v0';
    private string $apiKey;
    private string $apiSecret;
    private bool $ignoreCertificate = false;

    /**
     * Constructor that sets the apiKey and apiSecret using default apiUrl.
     * @param $apiKey The API key from the customers' portal.
     * @param $apiSecret The API secret from the customers' portal.
     */
    public function __construct($apiKey, $apiSecret)
    {
        $this->apiKey = trim($apiKey);
        $this->apiSecret = trim($apiSecret);
    }

    /**
     * Sets and overrides the default apiUrl, useful during development.
     * @param $apiUrl The URL of NovoServe's API.
     * @return $this Returns Client object.
     */
    public function setApiUrl($apiUrl): Client
    {
        $this->apiUrl = rtrim(trim($apiUrl), '/');
        return $this;
    }

    /**
     * Whether to ignore potential certificate errors/issues, useful during development.
     * @param bool $ignoreCertificate Ignore or not to ignore.
     * @return $this Returns Client object.
     */
    public function ignoreCertificate(bool $ignoreCertificate): Client
    {
        $this->ignoreCertificate = $ignoreCertificate;
        return $this;
    }

    /**
     * Internal cURL function to execute the actual request.
     * @param string $method The HTTP method to use.
     * @param string $endpoint The target endpoint.
     * @param array $body The body content which are the parameters.
     * @return array Returns an array of data returned by the API.
     */
    private function curl(string $method, string $endpoint, array $body = []): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiUrl . '/' . ltrim($endpoint, '/'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERPWD => $this->apiKey . ':' . $this->apiSecret,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        if ($method != 'GET') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body, JSON_PRETTY_PRINT));
        }

        if ($this->ignoreCertificate) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        }

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $responseArray = json_decode($response, true);
        if ($info['http_code'] < 200 || $info['http_code'] > 399) {
            $responseError = $responseArray['results'] ?? 'Unknown error';
            throw new \Exception($responseError);
        }
        curl_close($curl);
        return $responseArray;
    }

    /**
     * Function that executes a GET request.
     * @param string $endpoint The target endpoint.
     * @param array $body The body data to send.
     * @return array Returns an array with data from the API.
     */
    public function get(string $endpoint, array $body = []): array
    {
        if (count($body)) {
            $endpoint .= '?' . http_build_query($body);
        }
        return $this->curl('GET', $endpoint);
    }

    /**
     * Function that executes a POST request.
     * @param string $endpoint The target endpoint.
     * @param array $body The body data to send.
     * @return array Returns an array with data from the API.
     */
    public function post(string $endpoint, array $body = []): array
    {
        return $this->curl('POST', $endpoint, $body);
    }

    /**
     * Function that executes a PUT request.
     * @param string $endpoint The target endpoint.
     * @param array $body The body data to send.
     * @return array Returns an array with data from the API.
     */
    public function put(string $endpoint, array $body = []): array
    {
        return $this->curl('PUT', $endpoint, $body);
    }

    /**
     * Function that executes a PATCH request.
     * @param string $endpoint The target endpoint.
     * @param array $body The body data to send.
     * @return array Returns an array with data from the API.
     */
    public function patch(string $endpoint, array $body = []): array
    {
        return $this->curl('PATCH', $endpoint, $body);
    }

    /**
     * Function that executes a DELETE request.
     * @param string $endpoint The target endpoint.
     * @param array $body The body data to send.
     * @return array Returns an array with data from the API.
     */
    public function delete(string $endpoint, array $body = []): array
    {
        return $this->curl('DELETE', $endpoint, $body);
    }

}
