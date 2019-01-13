# GoogleAnalyticsRequester

Wrapper for querying GoogleAnalytics API, getting the results and returning as JSON with a bit of post processing.

It makes use of the [Google APIs Client Library for PHP](https://github.com/googleapis/google-api-php-client).

## Install

1. Clone the repo
  ```
  git clone https://github.com/XaviArnaus/GoogleAnalyticsRequester.git
  ```
2. Move to the folder
  ```
  cd GoogleAnalyticsRequester
  ```
3. Run composer install on it
  ```
  composer install
  ```
4. Place the `service-account-credentials.json` in the root of the project, following the steps in [Google Reporting API authorisation](https://developers.google.com/analytics/devguides/reporting/core/v3/coreDevguide#before).
5. Start making requests to the script. You can fire the PHP interpreter or point a Web Browser to this script.

## API

This is a call to a script with GET parameters that returns an array of values as a JSON. That's all.

The following are the available parameters and their defaults:

GET parameter | possible values | defaults | comment
---|---|---|---
metrics|string|`ga:users`|Which metrics to query. Can be a list of comma separated metrics.
from|string|`14daysAgo`|Data range start for queried metrics
to|string|`today`|Data range end for queried metrics
dimensions|string|`ga:date`|Which dimensions are used to divide the queried metrics. `ga:date` means that you'll get a row per day.
sort|string|`-ga:date`|Wich dimension will be used to sort the queried metrics. Note that the minus in front means DESC order.
simplify|int|0|Discards data from result, one each every `n` defined. A `0` means no simplification done. Useful for huge resultsets.

For an exhaustive reference of *metrics* and *dimensions* visit the [official documentation](https://developers.google.com/analytics/devguides/reporting/core/dimsmets) and play with the [Query Explorer](https://ga-dev-tools.appspot.com/query-explorer/)
