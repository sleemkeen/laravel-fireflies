<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sleemkeen\Fireflies\Fireflies;
use Illuminate\Http\JsonResponse;

class FireFliesController extends Controller
{
    /**
     * Default fields for user queries
     */
    private $userFields = [
        'user_id',
        'email',
        'name',
        'num_transcripts',
        'recent_transcript',
        'recent_meeting',
        'minutes_consumed',
        'is_admin',
        'integrations'
    ];

    /**
     * Default fields for bite queries
     */
    private $biteFields = [
        'id',
        'name',
        'start_time',
        'end_time',
        'status',
        'preview',
        'privacies',
        'user' => [
            'name',
            'id'
        ],
        'captions' => [
            'text',
            'speaker_name',
            'start_time',
            'end_time'
        ]
    ];

    /**
     * Default fields for transcripts
     */
    private $transcriptFields = [
        'id',
        'title',
        'date',
        'duration',
        'sentences' => [
            'index',
            'text',
            'speaker_id',
            'speaker_name',
            'start_time',
            'end_time'
        ]
    ];

    /**
     * Get current user information
     *
     * @return JsonResponse
     */
    public function getCurrentUser(): JsonResponse
    {
        try {
            $user = Fireflies::getUser(null, $this->userFields);
            return response()->json(['data' => $user]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get user information by ID
     *
     * @param string $userId
     * @return JsonResponse
     */
    public function getUser(string $userId): JsonResponse
    {
        try {
            $user = Fireflies::getUser($userId, $this->userFields);
            return response()->json(['data' => $user]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get all transcripts
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTranscripts(Request $request): JsonResponse
    {
        try {
            $fields = [
                'id',
                'sentences' => [
                    'index',
                    'ai_filters' => [
                        'metric',
                    ]
                ],
                'speakers' => [
                    'id',
                    'name'
                ]
            ];
            
            $transcripts = Fireflies::getTranscripts($fields);
            return response()->json(['data' => $transcripts]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get bite information
     *
     * @param string $biteId
     * @return JsonResponse
     */
    public function getBite(string $biteId): JsonResponse
    {
        try {
            $bite = Fireflies::getBite($biteId);
            return response()->json(['data' => $bite]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get transcript bites
     *
     * @param string $transcriptId
     * @return JsonResponse
     */
    public function getTranscriptBites(string $transcriptId): JsonResponse
    {
        try {
            $bites = Fireflies::getTranscriptBites($transcriptId, $this->biteFields);
            return response()->json(['data' => $bites]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Upload audio file
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAudio(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'url' => 'required|url',
                'title' => 'sometimes|string',
                'attendees' => 'sometimes|array'
            ]);

            $options = array_filter($request->only(['title', 'attendees']));
            $result = Fireflies::uploadAudio($request->url, $options);
            
            return response()->json(['data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get meeting summary
     *
     * @param string $meetingId
     * @return JsonResponse
     */
    public function getMeetingSummary(string $meetingId): JsonResponse
    {
        try {
            $fields = [
                'id',
                'sections' => [
                    'title',
                    'content',
                    'type',
                    'confidence'
                ],
                'keyPoints',
                'topics',
                'actionItems',
                'questions',
                'status',
                'created_at',
                'updated_at'
            ];
            
            $summary = Fireflies::getMeetingSummary($meetingId, $fields);
            return response()->json(['data' => $summary]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Create a new bite
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createBite(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'transcript_id' => 'required|string',
                'start_time' => 'required|numeric',
                'end_time' => 'required|numeric',
                'name' => 'required|string',
                'privacies' => 'sometimes|array'
            ]);

            $bite = Fireflies::createBite($request->all());
            return response()->json(['data' => $bite]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Update bite privacy settings
     *
     * @param Request $request
     * @param string $biteId
     * @return JsonResponse
     */
    public function updateBitePrivacy(Request $request, string $biteId): JsonResponse
    {
        try {
            $request->validate([
                'privacies' => 'required|array'
            ]);

            $result = Fireflies::updateBitePrivacy($biteId, $request->privacies);
            return response()->json(['data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get user stats
     *
     * @param string $userId
     * @return JsonResponse
     */
    public function getUserStats(string $userId): JsonResponse
    {
        try {
            $fields = [
                'user_id',
                'num_transcripts',
                'minutes_consumed',
                'recent_transcript',
                'recent_meeting'
            ];
            
            $stats = Fireflies::getUserStats($userId, $fields);
            return response()->json(['data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
