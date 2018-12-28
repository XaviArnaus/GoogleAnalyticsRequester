<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load the Google API PHP Client Library.
require_once __DIR__ . '/vendor/autoload.php';

$defaults = [
  'metrics' => 'ga:users',
  'from' => '14daysAgo',
  'to' => 'today',
  'dimensions' => 'ga:date',
  'sort' => '-ga:date'
];

$analytics = initializeAnalytics();
$profile = getFirstProfileId($analytics);
$results = getResults($analytics, $profile);

printJsonResults($results);

function getQueryParameters()
{
  $parameters = [];
  $parameters['metrics'] = isset($_GET['metrics']) ? $_GET['metrics'] : $defaults['metrics'];
  $parameters['from'] = isset($_GET['from']) ? $_GET['from'] : $defaults['from'];
  $parameters['to'] = isset($_GET['to']) ? $_GET['to'] : $defaults['to'];
  $parameters['dimensions'] = isset($_GET['dimensions']) ? $_GET['dimensions'] : $defaults['dimensions'];
  $parameters['sort'] = isset($_GET['sort']) ? $_GET['sort'] : $defaults['sort'];

  return $parameters;
}

function initializeAnalytics()
{
  // Creates and returns the Analytics Reporting service object.

  // Use the developers console and download your service account
  // credentials in JSON format. Place them in this directory or
  // change the key file location if necessary.
  $KEY_FILE_LOCATION = __DIR__ . '/service-account-credentials.json';

  // Create and configure a new client object.
  $client = new Google_Client();
  $client->setApplicationName("Hello Analytics Reporting");
  $client->setAuthConfig($KEY_FILE_LOCATION);
  $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
  $analytics = new Google_Service_Analytics($client);

  return $analytics;
}

function getFirstProfileId($analytics) {
  // Get the user's first view (profile) ID.

  // Get the list of accounts for the authorized user.
  $accounts = $analytics->management_accounts->listManagementAccounts();

  if (count($accounts->getItems()) > 0) {
    $items = $accounts->getItems();
    $firstAccountId = $items[0]->getId();

    // Get the list of properties for the authorized user.
    $properties = $analytics->management_webproperties
        ->listManagementWebproperties($firstAccountId);

    if (count($properties->getItems()) > 0) {
      $items = $properties->getItems();
      $firstPropertyId = $items[0]->getId();

      // Get the list of views (profiles) for the authorized user.
      $profiles = $analytics->management_profiles
          ->listManagementProfiles($firstAccountId, $firstPropertyId);

      if (count($profiles->getItems()) > 0) {
        $items = $profiles->getItems();

        // Return the first view (profile) ID.
        return $items[0]->getId();

      } else {
        throw new Exception('No views (profiles) found for this user.');
      }
    } else {
      throw new Exception('No properties found for this user.');
    }
  } else {
    throw new Exception('No accounts found for this user.');
  }
}

function getResults($analytics, $profileId) {
  // Calls the Core Reporting API and queries for the number of sessions
  // for the last seven days.
   $parameters = getQueryParameters();
   return $analytics->data_ga->get(
      'ga:' . $profileId,
      $parameters['from'],
      $parameters['to'],
      $parameters['metrics'],
      [
        'dimensions' => $parameters['dimensions'],
        'sort' => $parameters['sort']
      ]
   );
}

function printJsonResults($result) {
  $cleaned = [];
  foreach($result->getRows() as $row) {
    $date = DateTime::createFromFormat('Ymd', $row[0]);
    $cleaned[]=array_merge([$date->format('Y-m-d')], array_map(function($value) { return intval($value); }, array_slice($row, 1)));
  }
  print json_encode($cleaned);
}
