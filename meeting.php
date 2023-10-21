<?php

// Include the Google API Client Library for PHP
require 'path/to/google-api-php-client/vendor/autoload.php';

// Your Google API credentials
$client_id = 'YOUR_CLIENT_ID';
$client_secret = 'YOUR_CLIENT_SECRET';
$redirect_uri = 'YOUR_REDIRECT_URI';

// Initialize the Google API Client
$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->setAccessType('offline');

// Initialize the Google Calendar service
$service = new Google_Service_Calendar($client);

// Function to create a Google Meet event
function createGoogleMeetEvent($summary, $startDateTime, $endDateTime) {
    global $service;

    $event = new Google_Service_Calendar_Event(array(
        'summary' => $summary,
        'start' => array('dateTime' => $startDateTime),
        'end' => array('dateTime' => $endDateTime),
        'conferenceData' => array(
            'createRequest' => array(
                'requestId' => 'unique-request-id',
            ),
        ),
    ));

    $event = $service->events->insert('primary', $event, array('conferenceDataVersion' => 1));

    return $event->hangoutLink;
}

// Function to get the event ID for a Google Meet meeting
function getEventIdForMeeting($meetingLink) {
    global $service;

    $urlParts = parse_url($meetingLink);
    parse_str($urlParts['query'], $query);
    $meetingId = $query['id'];

    $calendarId = 'primary';
    $optParams = array(
        'q' => 'conferenceData.meetingId:' . $meetingId,
    );

    $events = $service->events->listEvents($calendarId, $optParams);

    if (count($events->getItems()) > 0) {
        $event = $events->getItems()[0];
        return $event->getId();
    }

    return null; // Meeting not found
}

// Example usage to create an event and retrieve its event ID
$startDateTime = '2023-10-21T15:00:00Z'; // Replace with your desired start date and time
$endDateTime = '2023-10-21T16:00:00Z'; // Replace with your desired end date and time
$meetingLink = createGoogleMeetEvent('Meeting Title', $startDateTime, $endDateTime);
echo "Meeting Link: $meetingLink\n";

// Retrieve the event ID for the created meeting
$eventID = getEventIdForMeeting($meetingLink);

if ($eventID) {
    echo "Event ID: $eventID\n";
} else {
    echo "Meeting not found in your calendar.\n";
}
?>
