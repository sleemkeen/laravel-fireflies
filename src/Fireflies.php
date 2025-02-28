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

use GraphQL\Query;
use GraphQL\Variable;
use GraphQL\Client as GraphQLClient;
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

    protected $baseUrl = 'https://api.fireflies.ai/graphql';
    /**
     *  Response from requests made to Paystack
     * @var mixed
     */

    protected $response;

    private static $instance = null;

    /**
     * Default selection sets for different query types
     */
    private static $defaultSelections = [
        'user' => [
            'user_id',
            'email',
            'name',
            'num_transcripts',
            'recent_transcript',
            'recent_meeting',
            'minutes_consumed',
            'is_admin',
            'integrations'
        ],
        'summary' => [
            'id',
            'sections' => [
                'title',
                'content'
            ],
            'keyPoints'
        ],
        'transcript' => [
            'id',
            'title',
            'date',
            'sentences' => [
                'text',
                'speaker',
                'timestamp'
            ]
        ]
    ];

    public function __construct($apiKey = null)
    {
        $this->setKey($apiKey);
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
    public function setKey($apiKey = null)
    {
        if (is_array($apiKey)) {
            $this->secretKey = $apiKey['api_key'] ?? config('fireflies.api_key');
        } else {
            $this->secretKey = $apiKey ?? config('fireflies.api_key');
        }

        if (!$this->secretKey) {
            throw new IsNullException('No API Key provided. Please set api_key in your config/fireflies.php file or provide it directly.');
        }
    }

    /**
     * Set options for making the Client request
     */
    private function setRequestOptions()
    {
        if (!$this->secretKey) {
            throw new IsNullException('No API Key provided. Please provide your Fireflies API Key');
        }

        $this->client = new GraphQLClient(
            $this->baseUrl,
            [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        );
    }

    /**
     * Execute GraphQL query
     *
     * @param Query|array $query
     * @return array
     */
    private function executeQuery($query)
    {
        try {
            if ($query instanceof Query) {
                // Handle Query object
                $response = $this->client->runQuery($query);
            } else {
                // Handle raw query array
                $queryString = $query['query'];
                $response = $this->client->runRawQuery($queryString, $query['variables'] ?? null);
            }
            
            $data = $response->getData();
            
            // Check for errors in the response data
            if (isset($data['errors'])) {
                throw new IsNullException($data['errors'][0]['message'] ?? 'GraphQL query error');
            }
            
            return $data;
        } catch (\Exception $e) {
            throw new IsNullException($e->getMessage());
        }
    }

    /**
     * Get or create the singleton instance
     */
    private static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Build a Query object with nested selection sets
     *
     * @param string $queryName
     * @param array $arguments
     * @param array $selections
     * @return Query
     */
    private static function buildQuery($queryName, $arguments = [], $selections = [])
    {
        $query = new Query($queryName);
        
        if (!empty($arguments)) {
            $query->setArguments($arguments);
        }

        $selectionSet = $selections ?: (self::$defaultSelections[$queryName] ?? []);
        $query->setSelectionSet(self::buildSelectionSet($selectionSet));

        return $query;
    }

    /**
     * Recursively build selection sets for nested queries
     *
     * @param array $selections
     * @return array
     */
    private static function buildSelectionSet($selections)
    {
        $set = [];
        foreach ($selections as $key => $value) {
            if (is_array($value)) {
                if (is_string($key)) {
                    $set[] = (new Query($key))->setSelectionSet(self::buildSelectionSet($value));
                } else {
                    $set[] = $value;
                }
            } else {
                $set[] = $value;
            }
        }
        return $set;
    }

    /**
     * Build nested GraphQL query
     *
     * @param string $queryName The main query name
     * @param array $fields The fields with nested structure
     * @param array $arguments Optional arguments for the query
     * @return array|null
     */
    private static function buildNestedQuery(string $queryName, array $fields, array $arguments = [])
    {
        $query = self::buildQuery($queryName, $arguments, $fields);
        $response = self::getInstance()->client->runQuery($query);
        return self::convertResponseToArray($response->getData()->$queryName);
    }

    /**
     * Get meeting transcripts
     *
     * @param array $fields Required fields to retrieve
     * @return array|null
     */
    public static function getTranscripts(array $fields)
    {
        return self::buildNestedQuery('transcripts', $fields);
    }

    /**
     * Upload meeting audio
     *
     * @param string $audioUrl
     * @param array $options
     * @return array|null
     */
    public static function uploadAudio($audioUrl, $options = [])
    {
        $fields = [
            'id',
            'status',
            'message',
            'title',
            'duration',
            'source',
            'workspace_id'
        ];

        $variables = array_merge(['url' => $audioUrl], $options);
        return self::buildNestedQuery('uploadAudio', $fields, $variables);
    }

    /**
     * Get meeting summary
     *
     * @param string $meetingId
     * @param array $fields Required fields to retrieve
     * @return array|null
     */
    public static function getMeetingSummary($meetingId, array $fields)
    {
        return self::buildNestedQuery('summary', $fields, ['meetingId' => $meetingId]);
    }

    /**
     * Get user information
     *
     * @param string|null $userId
     * @param array $fields Required fields to retrieve
     * @return array|null
     */
    public static function getUser($userId = null, array $fields)
    {
        $arguments = $userId ? ['id' => $userId] : [];
        return self::buildNestedQuery('user', $fields, $arguments);
    }

    /**
     * Get user's integrations
     *
     * @param string $userId
     * @return array|null
     */
    public static function getUserIntegrations($userId)
    {
        $fields = [
            'user_id',
            'integrations'
        ];
        return self::buildNestedQuery('user', $fields, ['id' => $userId]);
    }

    /**
     * Get user's transcript statistics
     *
     * @param string $userId
     * @param array $fields Required fields to retrieve
     * @return array|null
     */
    public static function getUserStats($userId, array $fields)
    {
        return self::buildNestedQuery('user', $fields, ['id' => $userId]);
    }

    /**
     * Get bite information
     *
     * @param string $biteId
     * @param array $fields Required fields to retrieve
     * @return array|null
     */
    public static function getBite($biteId, array $fields)
    {
        return self::buildNestedQuery('bite', $fields, ['id' => $biteId]);
    }

    /**
     * Create a new bite
     *
     * @param array $biteData
     * @return array|null
     */
    public static function createBite($biteData)
    {
        $fields = [
            'id',
            'status',
            'name',
            'preview'
        ];

        $arguments = [
            'transcript_id' => $biteData['transcript_id'],
            'start_time' => $biteData['start_time'],
            'end_time' => $biteData['end_time'],
            'name' => $biteData['name'],
            'privacies' => $biteData['privacies'] ?? ['team']
        ];

        return self::buildNestedQuery('createBite', $fields, $arguments);
    }

    /**
     * Update bite privacy settings
     *
     * @param string $biteId
     * @param array $privacies
     * @return array|null
     */
    public static function updateBitePrivacy($biteId, array $privacies)
    {
        $fields = [
            'id',
            'privacies'
        ];

        $arguments = [
            'id' => $biteId,
            'privacies' => $privacies
        ];

        return self::buildNestedQuery('updateBitePrivacy', $fields, $arguments);
    }

    /**
     * Get transcript bites
     *
     * @param string $transcriptId
     * @param array $fields Required fields to retrieve
     * @return array|null
     */
    public static function getTranscriptBites($transcriptId, array $fields)
    {
        return self::buildNestedQuery('transcript', ['bites' => $fields], ['id' => $transcriptId]);
    }

    /**
     * Convert stdClass response to array
     *
     * @param mixed $data
     * @return array|null
     */
    private static function convertResponseToArray($data)
    {
        if (!$data) {
            return null;
        }

        if (is_array($data)) {
            return array_map([self::class, 'convertResponseToArray'], $data);
        }

        if (is_object($data)) {
            return array_map([self::class, 'convertResponseToArray'], get_object_vars($data));
        }

        return $data;
    }
}
