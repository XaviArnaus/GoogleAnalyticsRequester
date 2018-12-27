<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load the Google API PHP Client Library.
require_once __DIR__ . '/vendor/autoload.php';

$header_dict = [
  "ga:date" => "Date",
  "ga:newUsers" => "New Users",
  "ga:users" => "Users",
  "ga:sessions" => "Sessions",
  "ga:goal6Completions" => "AdMob",
  "ga:goal7Completions" => "Amazon",
  "ga:goal8Completions" => "AppNext",
  "ga:goal9Completions" => "Fallout"
];

$analytics = initializeAnalytics();
$profile = getFirstProfileId($analytics);
$results = getResults($analytics, $profile);
//printDataTable($results);
printJsonResults($results);

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
  $optParams = array(
      'dimensions' => 'ga:date',
      'sort' => '-ga:date',
      'max-results' => '25');
  return $analytics->data_ga->get(
     'ga:' . $profileId,
     '14daysAgo',
     'today',
     'ga:newUsers,ga:users,ga:sessions,ga:goal6Completions,ga:goal7Completions,ga:goal8Completions,ga:goal9Completions',
     $optParams
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

function printDataTable(&$results) {
  if (count($results->getRows()) > 0) {
    $table = '<table>';

    // Print headers.
    $table .= '<tr>';

    foreach ($results->getColumnHeaders() as $header) {
      $table .= '<th>' . $header->name . '</th>';
    }
    $table .= '</tr>';

    // Print table rows.
    foreach ($results->getRows() as $row) {
      $table .= '<tr>';
        foreach ($row as $cell) {
          $table .= '<td>'
                 . htmlspecialchars($cell, ENT_NOQUOTES)
                 . '</td>';
        }
      $table .= '</tr>';
    }
    $table .= '</table>';

  } else {
    $table .= '<p>No Results Found.</p>';
  }
  print $table;
}

function printResults($results) {
  // Parses the response from the Core Reporting API and prints
  // the profile name and total sessions.
  if (count($results->getRows()) > 0) {

    // Get the profile name.
    $profileName = $results->getProfileInfo()->getProfileName();

    // Get the entry for the first entry in the first row.
    $rows = $results->getRows();
    $sessions = $rows[0][0];

    // Print the results.
    print "First view (profile) found: $profileName\n";
    print "Total sessions: $sessions\n";
    var_dump($rows);
  } else {
    print "No results found.\n";
  }
}
