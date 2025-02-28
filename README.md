I'll update the README.md to document the new flexible field selection feature and provide examples:

```markdown:packages/laravel-fireflies/README.md
# Laravel Fireflies

A Laravel package for interacting with the Fireflies.ai GraphQL API.

## Installation

```bash
composer require sleemkeen/laravel-fireflies
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Sleemkeen\Fireflies\FirefliesServiceProvider"
```

Add your Fireflies API key to your `.env` file:

```
FIREFLIES_API_KEY=your-api-key
```

## Usage

### Basic Usage

```php
use Sleemkeen\Fireflies\Fireflies;

// Get user information
$fields = [
    'user_id',
    'email',
    'name',
    'num_transcripts'
];
$user = Fireflies::getUser(null, $fields);

// Get specific user
$userId = 'user123';
$user = Fireflies::getUser($userId, $fields);
```

### Working with Transcripts

```php
// Get transcripts with nested fields
$fields = [
    'id',
    'title',
    'date',
    'sentences' => [
        'index',
        'text',
        'speaker_id',
        'start_time',
        'end_time',
        'ai_filters' => [
            'metric'
        ]
    ],
    'speakers' => [
        'id',
        'name'
    ]
];

$transcripts = Fireflies::getTranscripts($fields);
```

### Working with Bites

```php
// Get bite information
$fields = [
    'id',
    'name',
    'status',
    'user' => [
        'name',
        'id'
    ],
    'captions' => [
        'text',
        'speaker_name',
        'start_time'
    ]
];

$bite = Fireflies::getBite($biteId, $fields);

// Get transcript bites
$bites = Fireflies::getTranscriptBites($transcriptId, $fields);
```

### Meeting Summaries

```php
// Get meeting summary
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
    'actionItems'
];

$summary = Fireflies::getMeetingSummary($meetingId, $fields);
```

### User Statistics

```php
// Get user stats
$fields = [
    'user_id',
    'num_transcripts',
    'minutes_consumed',
    'recent_transcript',
    'recent_meeting'
];

$stats = Fireflies::getUserStats($userId, $fields);
```

### Upload Audio

```php
$options = [
    'title' => 'Meeting Title',
    'attendees' => ['user1@example.com', 'user2@example.com']
];

$result = Fireflies::uploadAudio('https://example.com/audio.mp3', $options);
```

## Field Selection

All query methods support flexible field selection through arrays. You can:

- Select specific fields: `['id', 'name', 'email']`
- Include nested fields: `['user' => ['id', 'name']]`
- Deep nesting: `['sentences' => ['ai_filters' => ['metric']]]`

## Error Handling

The package throws `IsNullException` for:
- Missing API key
- Invalid queries
- API errors

```php
try {
    $user = Fireflies::getUser($userId, $fields);
} catch (IsNullException $e) {
    // Handle error
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
```

This README now:
1. Documents the flexible field selection feature
2. Shows examples of nested queries
3. Provides clear usage examples
4. Explains error handling
5. Includes configuration instructions

Let me know if you'd like me to add or modify anything!
