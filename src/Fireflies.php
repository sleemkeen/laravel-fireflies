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

use GuzzleHttp\Client as HttpClient;
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
     * @var HttpClient
     */
    protected $client;

    /**
     * Fireflies API base Url
     * @var string
     */
    protected $baseUrl = 'https://api.fireflies.ai/graphql';

    /**
     * Response from requests made to Paystack
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
        ],
        'bites' => [
            'transcript_id',
            'name',
            'id',
            'thumbnail',
            'preview',
            'status',
            'summary',
            'user_id',
            'start_time',
            'end_time',
            'summary_status',
            'media_type',
            'created_at',
            'created_from' => [
                'description',
                'duration',
                'id',
                'name',
                'type'
            ],
            'captions' => [
                'end_time',
                'index',
                'speaker_id',
                'speaker_name',
                'start_time',
                'text'
            ],
            'sources' => [
                'src',
                'type'
            ],
            'privacies',
            'user' => [
                'first_name',
                'last_name',
                'picture',
                'name',
                'id'
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

        $this->client = new HttpClient([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
        ]);
    }

    /**
     * Execute GraphQL query
     *
     * @param array $query
     * @return array
     */
    private function executeQuery($query)
    {
        try {
            $queryString = $query['query'];
            $variables = $query['variables'] ?? [];

            $response = $this->client->post('', [
                'json' => [
                    'query' => $queryString,
                    'variables' => $variables
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['errors'])) {
                throw new IsNullException($data['errors'][0]['message'] ?? 'GraphQL query error');
            }

            return $data['data'];
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
     * Build a GraphQL query string
     *
     * @param string $queryName
     * @param array $arguments
     * @param array $selections
     * @param array $changeableInnerQuery
     * @return string
     */
    private static function buildQueryString($queryName, $arguments = [], $selections = [], $changeableInnerQuery = [])
    {
        $selectionSet = $selections ?: (self::$defaultSelections[$queryName] ?? []);
        
        // Convert queryName to proper case format
        $operationName = ucfirst($queryName); // For the operation name
        
        // Build variable definitions for the query
        $variableDefinitions = [];
        $variableUsage = [];
        if (!empty($arguments)) {
            foreach ($arguments as $key => $value) {
                // Use the specified type from changeableInnerQuery if available
                $type = isset($changeableInnerQuery['types']) 
                    ? $changeableInnerQuery['types']
                    : (is_string($value) ? 'String!' : 'ID!');
                    
                $variableDefinitions[] = '$' . $key . ': ' . $type;
                $variableUsage[] = $key . ': $' . $key;
            }
        }

        // Build the query string
        $queryString = "query";
        if (!empty($variableDefinitions)) {
            $queryString .= " $operationName(" . implode(', ', $variableDefinitions) . ")";
        }
        
        // Convert field name to lowercase if specified
        $fieldName = isset($changeableInnerQuery['isLowerCase']) && $changeableInnerQuery['isLowerCase'] 
            ? lcfirst($queryName) 
            : $queryName;
        
        $queryString .= " { $fieldName";
        
        // Add variable usage in the query
        if (!empty($variableUsage) && empty($changeableInnerQuery)) {
            $queryString .= '(' . implode(', ', $variableUsage) . ')';
        } else if (!empty($changeableInnerQuery['innerQuery'])) {
            $queryString .= '(' . implode(', ', $changeableInnerQuery['innerQuery']) . ')';
        }
        
        $selectionString = self::buildSelectionString($selectionSet);
        $queryString .= " { $selectionString } }";
        
        return $queryString;
    }

    /**
     * Build a GraphQL mutation string
     *
     * @param string $mutationName
     * @param array $arguments
     * @param array $selections
     * @return string
     */
    private static function buildMutationString($mutationName, $arguments = [], $selections = [])
    {
        $argsString = self::buildArgumentsString($arguments);
        $selectionString = self::buildSelectionString($selections);

        return "mutation $mutationName $argsString { $mutationName $argsString { $selectionString } }";
    }

    /**
     * Build arguments string for GraphQL query/mutation
     *
     * @param array $arguments
     * @return string
     */
    private static function buildArgumentsString($arguments)
    {
        if (empty($arguments)) {
            return '';
        }

        $args = [];
        foreach ($arguments as $key => $value) {
            $args[] = "$key: " . json_encode($value);
        }

        return '(' . implode(', ', $args) . ')';
    }

    /**
     * Build selection string for GraphQL query/mutation
     *
     * @param array $selections
     * @return string
     */
    private static function buildSelectionString($selections)
    {
        $set = [];
        foreach ($selections as $key => $value) {
            if (is_array($value)) {
                $set[] = "$key { " . self::buildSelectionString($value) . " }";
            } else {
                $set[] = $value;
            }
        }
        return implode(' ', $set);
    }

    /**
     * Build nested GraphQL query
     *
     * @param string $queryName The main query name
     * @param array $fields The fields with nested structure
     * @param array $arguments Optional arguments for the query
     * @param array $changeableInnerQuery Optional arguments for the inner query
     * @return array|null
     */
    private static function buildNestedQuery(string $queryName, array $fields, array $arguments = [], array $changeableInnerQuery = [])
    {
        $queryString = self::buildQueryString($queryName, $arguments, $fields, $changeableInnerQuery);
       
        $response = self::getInstance()->executeQuery([
            'query' => $queryString,
            'variables' => (object)($arguments ?: [])  // Always send an object, even if empty
        ]);
        return self::convertResponseToArray($response);
    }

    // ... existing code ...

private static function buildComplexQueryString($queryName, $arguments = [], $selections = [])
{
    $selectionSet = $selections ?: (self::$defaultSelections[$queryName] ?? []);
    
    // Convert queryName to proper case format
    $operationName = ucfirst($queryName); // For the operation name
    $fieldName = lcfirst($queryName);     // For the actual field name
    
    // Build variable definitions for the query
    $variableDefinitions = [];
    $variableUsage = [];
    if (!empty($arguments)) {
        foreach ($arguments as $key => $value) {
            // Determine the GraphQL type based on the argument name
            $type = $key === 'id' ? 'ID!' : 'String!';  // Default to String! for other cases
            $variableDefinitions[] = '$' . $key . ': ' . $type;
            $variableUsage[] = $key . ': $' . $key;
        }
    }

    // Build the query string
    $queryString = "query";
    if (!empty($variableDefinitions)) {
        $queryString .= " $operationName(" . implode(', ', $variableDefinitions) . ")";
    }
    
    $queryString .= " { $fieldName";
    
    // Add variable usage in the query
    if (!empty($variableUsage)) {
        $queryString .= '(' . implode(', ', $variableUsage) . ')';
    }
    
    $selectionString = self::buildSelectionString($selectionSet);
    $queryString .= " { $selectionString } }";
    
    return $queryString;
}

// ... existing code ...

    /**
     * Build nested GraphQL mutation
     *
     * @param string $mutationName The mutation name
     * @param array $fields The fields to return after mutation
     * @param array $arguments Arguments for the mutation
     * @return array|null
     */
    private static function buildNestedMutation(string $mutationName, array $fields, array $arguments = [])
    {
        $mutationString = self::buildMutationString($mutationName, $arguments, $fields);
        $response = self::getInstance()->executeQuery([
            'query' => $mutationString,
            'variables' => $arguments
        ]);
        return self::convertResponseToArray($response);
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
     * @param string $audioUrl URL of the audio file
     * @param array $options Additional options (title, attendees, webhook, custom_language, etc.)
     * @return array|null
     */
    public static function uploadAudio($audioUrl, $options = [])
    {
        $fields = [
            'success',
            'title',
            'message'
        ];

        // Prepare input object according to AudioUploadInput type
        $input = array_merge([
            'url' => $audioUrl,
        ], $options);

        $query = [
            'query' => 'mutation($input: AudioUploadInput) { 
                uploadAudio(input: $input) { 
                    success 
                    title 
                    message 
                } 
            }',
            'variables' => [
                'input' => $input
            ]
        ];

        $response = self::getInstance()->executeQuery($query);
        return self::convertResponseToArray($response);
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
        return 'Api not available yet';
    }

    /**
     * Get user information (new implementation)
     *
     * @param array $fields Required fields to retrieve
     * @param string|null $userId Optional user ID
     * @return array|null
     */
    public static function getUser(array $fields, $userId = null)
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
        return self::buildNestedQuery(
            'bite', 
            $fields, 
            ['biteId' => $biteId],
            ['innerQuery' => ['id: $biteId'], 'types' => 'ID!', 'isLowerCase' => true]
         );
    }


    /**
     * Get all bites or user's bites
     *
     * @param bool $mine Whether to fetch only the user's bites
     * @param array $fields Required fields to retrieve
     * @return array|null
     */
    public static function getBites(array $fields = [], array $options = [])
    {
        return self::buildNestedQuery(
            'bites', 
            $fields,
            $options,
            ['innerQuery' => ['mine: $mine'], 'types' => 'Boolean', 'isLowerCase' => true]
        );
    }

       /**
     * Create a new bite
     *
     * @param string $transcriptId ID of the transcript
     * @param float $startTime Start time in seconds
     * @param float $endTime End time in seconds
     * @return array|null
     */
    public static function createBite(string $transcriptId, float $startTime, float $endTime)
    {
        $query = [
            'query' => 'mutation($transcriptId: ID!, $startTime: Float!, $endTime: Float!) {
                createBite(transcript_id: $transcriptId, start_time: $startTime, end_time: $endTime) {
                    status
                    name
                    id
                }
            }',
            'variables' => [
                'transcriptId' => $transcriptId,
                'startTime' => $startTime,
                'endTime' => $endTime
            ]
        ];

        $response = self::getInstance()->executeQuery($query);
        return self::convertResponseToArray($response);
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

        return self::buildNestedMutation('updateBitePrivacy', $fields, $arguments);
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
        return self::buildNestedQuery('Bite', $fields, ['biteId' => $transcriptId], ['innerQuery' => ['id: $biteId'], 'types' => 'ID!', 'isLowerCase' => true]);
    }

    /**
     * Delete a transcript
     *
     * @param string $transcriptId ID of the transcript to delete
     * @return array|null
     */
    public static function deleteTranscript(string $transcriptId)
    {
        $query = [
            'query' => 'mutation($id: String!) {
                deleteTranscript(id: $id) {
                    id
                    title
                    host_email
                    organizer_email
                    fireflies_users
                    participants
                    date
                    transcript_url
                    audio_url
                    duration
                }
            }',
            'variables' => [
                'id' => $transcriptId
            ]
        ];

        $response = self::getInstance()->executeQuery($query);
        return self::convertResponseToArray($response);
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

    /**
     * Set user role
     *
     * @param string $userId ID of the user
     * @param string $role Role to set ('admin' or other valid role)
     * @return array|null
     */
    public static function setUserRole(string $userId, string $role)
    {
        $query = [
            'query' => 'mutation($userId: String!, $role: Role!) {
                setUserRole(user_id: $userId, role: $role) {
                    id
                    name
                    email
                    role
                }
            }',
            'variables' => [
                'userId' => $userId,
                'role' => $role
            ]
        ];

        $response = self::getInstance()->executeQuery($query);
        return self::convertResponseToArray($response);
    }

       /**
     * Add Fireflies to a live meeting
     *
     * @param string $meetingLink URL of the meeting to join
     * @return array|null
     */
    public static function addToLiveMeeting(string $meetingLink)
    {
        $query = [
            'query' => 'mutation AddToLiveMeeting($meetingLink: String!) {
                addToLiveMeeting(meeting_link: $meetingLink) {
                    success
                }
            }',
            'variables' => [
                'meetingLink' => $meetingLink
            ]
        ];

        $response = self::getInstance()->executeQuery($query);
        return self::convertResponseToArray($response);
    }

    /**
     * Get a specific transcript
     *
     * @param string $transcriptId ID of the transcript
     * @param array $fields Required fields to retrieve
     * @return array|null
     */
    public static function getTranscript(string $transcriptId, array $fields)
    {
        return self::buildNestedQuery('transcript', $fields, ['id' => $transcriptId]);
    }

    /**
     * Get current user information
     *
     * @param array $fields Required fields to retrieve
     * @return array|null
     */
    public static function getCurrentUser(array $fields)
    {
        return self::buildNonNestedQuery('user', $fields);
    }

    /**
     * Build simple (non-nested) GraphQL query
     *
     * @param string $queryName The main query name
     * @param array $fields Simple array of field names
     * @param array $arguments Optional arguments for the query
     * @return array|null
     */
    private static function buildNonNestedQuery(string $queryName, array $fields, array $arguments = [])
    {
        // Build the base query string
        $queryString = "query";
        
        if (!empty($arguments)) {
            // Convert argument keys to match GraphQL variables format
            $variables = [];
            $variableDefinitions = [];
            $variableUsage = [];
            
            foreach ($arguments as $key => $value) {
                $varName = '${' . $key . '}';
                $type = is_string($value) ? 'String!' : 'ID!';  // Add more types as needed
                $variableDefinitions[] = '$' . $key . ': ' . $type;
                $variableUsage[] = $key . ': ' . $varName;
                $variables[$key] = $value;
            }

            // Add operation name and variable definitions
            $queryString .= " $queryName(" . implode(', ', $variableDefinitions) . ")";
            
            // Add fields with variables
            $fieldString = implode(' ', $fields);
            $queryString .= " { $queryName(" . implode(', ', $variableUsage) . ") { $fieldString } }";
        } else {
            // Simple query without variables
            $fieldString = implode(' ', $fields);
            $queryString .= " { $queryName { $fieldString } }";
        }

        return self::getInstance()->executeQuery([
            'query' => $queryString,
            'variables' => (object)($arguments ?: [])  // Always send an object, even if empty
        ]);
    }

    /**
     * Get AI Apps outputs
     *
     * @param array $options Array containing appId, transcriptId, skip, and limit
     * @param array $fields Required fields to retrieve
     * @return array|null
     */
    public static function getAIAppsOutputs(array $options, array $fields = [])
    {
        $defaultFields = [
            'outputs' => [
                'transcript_id',
                'user_id',
                'app_id',
                'created_at',
                'title',
                'prompt',
                'response'
            ]
        ];

        $selections = !empty($fields) ? $fields : $defaultFields;

        $query = [
            'query' => 'query GetAIAppsOutputs($appId: String, $transcriptId: String, $skip: Float, $limit: Float) {
                apps(app_id: $appId, transcript_id: $transcriptId, skip: $skip, limit: $limit) {
                    outputs {
                    transcript_id
                    user_id
                    app_id
                    created_at
                    title
                    prompt
                    response
                    }
                }
            }',
            'variables' => [
                'appId' => $options['app_id'] ?? null,
                'transcriptId' => $options['transcript_id'] ?? null,
                'skip' => $options['skip'] ?? null,
                'limit' => $options['limit'] ?? null
            ]
        ];

        $response = self::getInstance()->executeQuery($query);
        return self::convertResponseToArray($response);

    }
}
