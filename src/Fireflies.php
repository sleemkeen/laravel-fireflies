<?php

/*
 * This file is part of the Fireflies package.
 *
 * (c) Haruna Ahmadu <akhmadharuna@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sleemkeen\Fireflies;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Sleemkeen\Fireflies\Exceptions\IsNullException;

class Fireflies
{

     /**
     * Issue Secret Key from your Fireflies Dashboard
     * @var string
     */

    protected $secretKey;
    
     /**
     * Instance of Client
     * @var Client
     */

    protected $client;
     /**
     * Fireflies API base Url
     * @var string
     */

    protected $baseUrl;
    /**
     *  Response from requests made to Paystack
     * @var mixed
     */

    protected $response;

    public function __construct()
    {
        $this->setKey();
        $this->setBaseUrl();
        $this->setRequestOptions();
    }

    /**
     * Get Base Url from Fireflies config file
     */
    public function setBaseUrl()
    {
        $this->baseUrl = 'https://api.fireflies.ai/graphql';
    }

    /**
     * Get secret key from Fireflies config file
     */
    public function setKey()
    {
        $this->secretKey = Config::get('fireflies.secretKey');
    }

    /**
     * Set options for making the Client request
     */
    private function setRequestOptions()
    {
        $authBearer = 'Bearer ' . $this->secretKey;
        $this->client = new Client(
            [
                'base_uri' => $this->baseUrl,
                'headers' => [
                    'Authorization' => $authBearer,
                    'Content-Type'  => 'application/json'
                ]
            ]
        );
    }
    /**
     * @param array $body
     * @return Fireflies
     * @throws IsNullException
     */
    private function setHttpResponse($body)
    {
        $this->setRequestOptions();       
        $this->response = $this->client->post(
            $this->baseUrl,
            ["body" => $body]
        );

        return $this;
    }
    
     /**
     * @param array $body
     * @return Fireflies
     * @throws IsNullException
     */
    private function processAllArgumentToGraphQLQuery($body = [], $query)
    {
        $parseQuery = implode(" ", $body);
        $query = '{ "query": "{ '.$query.' { '.$parseQuery.' } }" }';
        return $query;
    }

    private function processSingleArgumentToGraphQLQuery($body = [], $query, $param)
    {
        $parseQuery = implode(" ", $body);
        $query = '{ "query": "{ '.$query.'(id:\"'.$param.'\") { '.$parseQuery.' } }" }';
        return $query;
    }

      /**
     * Get the whole response from a get operation
     * @return array
     */
    private function getResponse()
    {
        return json_decode($this->response->getBody(), true);
    }

    /**
     * Get the data response from a get operation
     * @return array
     */
    private function getData()
    {
        return $this->getResponse()['data'];
    }


    public function handleUserQuery($args = [], $param)
    {
        $data = $this->processSingleArgumentToGraphQLQuery($args, 'user', $param);
        return $this->setHttpResponse($data)->getData();
        
    }

    public function handleUsersQuery($args = [])
    {
        $data = $this->processAllArgumentToGraphQLQuery($args, 'users');
        return $this->setHttpResponse($data)->getData();
    }

    public function handleTranscriptQuery($args = [], $param)
    {
        $data = $this->processSingleArgumentToGraphQLQuery($args, 'transcript', $param);
        return $this->setHttpResponse($data)->getData();
    }

    public function handleTranscriptsQuery($args = [])
    {
        $data = $this->processAllArgumentToGraphQLQuery($args, 'transcripts');
        return $this->setHttpResponse($data)->getData();
    }
    

}