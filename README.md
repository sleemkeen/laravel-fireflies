```markdown:README.md
# Laravel Fireflies

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

I'll add a "Sample Routes" section to the README.md that documents all available API endpoints:

```markdown:README.md
# Laravel Fireflies

// ... (previous content remains the same) ...

## Available Routes

### User Routes
```
GET /fireflies/user                    # Get current user
GET /fireflies/user/{userId}          # Get specific user
POST /fireflies/user/{userId}/role    # Set user role
```

### Transcript Routes
```
GET    /fireflies/transcripts                    # Get all transcripts
GET    /fireflies/transcripts/{transcriptId}     # Get specific transcript
GET    /fireflies/transcript/{transcriptId}/bites # Get transcript bites
DELETE /fireflies/transcript/{transcriptId}      # Delete transcript
```

### Bite Routes
```
GET  /fireflies/bite                    # Get all bites
GET  /fireflies/bite/{biteId}          # Get specific bite
POST /fireflies/bite                    # Create new bite
PUT  /fireflies/bite/{biteId}/privacy  # Update bite privacy
```

### Meeting Routes
```
GET  /fireflies/audio/upload            # Upload audio file
GET  /fireflies/meeting/{meetingId}/summary # Get meeting summary
POST /fireflies/meeting/live            # Add to live meeting
```

### AI Apps Routes
```
GET /fireflies/apps/outputs            # Get AI Apps outputs
```

### Query Parameters

#### AI Apps Outputs
```
GET /fireflies/apps/outputs?app_id=123&transcript_id=456&skip=0&limit=10
```
- `app_id`: (optional) Filter by app ID
- `transcript_id`: (optional) Filter by transcript ID
- `skip`: (optional) Number of records to skip
- `limit`: (optional) Number of records to return

#### Bites
```
GET /fireflies/bite?mine=true
```
- `mine`: (optional) Filter to show only user's bites

// ... (rest of the README content remains the same) ...
```

This addition:
1. Organizes all routes by category
2. Shows the HTTP method for each route
3. Provides a brief description of each endpoint
4. Includes query parameter documentation for relevant endpoints
5. Maintains consistency with the existing documentation style
6. Makes it easy for developers to quickly find the endpoints they need

The routes section is placed before the detailed method documentation, providing a quick reference for available endpoints before diving into implementation details.


### User Management
```php
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

// Get meeting summary
$summary = Fireflies::getMeetingSummary($meetingId, $fields);
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
