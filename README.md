## Laravel Fireflies

A Laravel package for integrating with the Fireflies.ai API.

## Installation

You can install the package via composer:

```bash
composer require sleemkeen/laravel-fireflies
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Sleemkeen\Fireflies\FirefliesServiceProvider"
```

Add your Fireflies API key to your `.env` file:

```env
FIREFLIES_API_KEY=your-api-key
```

## Usage

### User Management
```php
use Sleemkeen\Fireflies\Fireflies;
// Get current user
$user = Fireflies::getCurrentUser($fields);

// Get specific user
$user = Fireflies::getUser($fields, $userId);

// Set user role
$result = Fireflies::setUserRole($userId, $role);
```

### Transcripts
```php
// Get all transcripts
$transcripts = Fireflies::getTranscripts($fields);

// Get specific transcript
$transcript = Fireflies::getTranscript($transcriptId, $fields);

// Delete transcript
$result = Fireflies::deleteTranscript($transcriptId);
```

### Bites
```php
// Get all bites
$bites = Fireflies::getBites($fields);

// Get specific bite
$bite = Fireflies::getBite($biteId, $fields);

// Get transcript bites
$bites = Fireflies::getTranscriptBites($transcriptId, $fields);

// Create bite
$bite = Fireflies::createBite($transcriptId, $startTime, $endTime);

// Update bite privacy
$result = Fireflies::updateBitePrivacy($biteId, $privacies);
```

### Meeting Management
```php
// Upload audio
$result = Fireflies::uploadAudio($audioUrl, $options);

// Add to live meeting
$result = Fireflies::addToLiveMeeting($meetingLink);

```

### AI Apps
```php
// Get AI Apps outputs
$options = [
    'app_id' => 'your-app-id',
    'transcript_id' => 'transcript-id',
    'skip' => 0,
    'limit' => 10
];
$outputs = Fireflies::getAIAppsOutputs($options, $fields);
```

## Available Methods

### User Methods
- `getCurrentUser(array $fields)`
- `getUser(array $fields, string $userId = null)`
- `setUserRole(string $userId, string $role)`

### Transcript Methods
- `getTranscripts(array $fields)`
- `getTranscript(string $transcriptId, array $fields)`
- `deleteTranscript(string $transcriptId)`

### Bite Methods
- `getBites(array $fields, array $options)`
- `getBite(string $biteId, array $fields)`
- `getTranscriptBites(string $transcriptId, array $fields)`
- `createBite(string $transcriptId, float $startTime, float $endTime)`
- `updateBitePrivacy(string $biteId, array $privacies)`

### Meeting Methods
- `uploadAudio(string $audioUrl, array $options)`
- `addToLiveMeeting(string $meetingLink)`
- `getMeetingSummary(string $meetingId, array $fields)`

### AI Apps Methods
- `getAIAppsOutputs(array $options, array $fields)`

## Testing


```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email akhmadharuna@gmail.com instead of using the issue tracker.

## Credits

- [Haruna Ahmadu](https://github.com/sleemkeen)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
```

This updated README:
1. Added the new AI Apps section with usage examples
2. Added the `getAIAppsOutputs` method to the Available Methods section
3. Maintained the existing structure while incorporating the new functionality
4. Removed any controller-specific implementation details to keep the focus on the package usage

The documentation now provides a clear overview of all available features, including the new AI Apps functionality, while maintaining a clean and professional format.
