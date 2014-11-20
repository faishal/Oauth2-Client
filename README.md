Oauth2-Client
=============

PHP Oauth2 Client Library 

Example

```php
spl_autoload_register(function ($class) {
    require str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
});


$client = new myOAuth2\Client(
        'CLIENT_ID',
        'CLIENT_SECRET',
        'CALLBACK_URL');

// configuration of service
$configuration = new myOAuth2\Service\Configuration(
        'AUTHORIZE_ENDPOINT',
        'ACCESS_TOKEN_ENDPOINT');

// storage class for access token, just implement myOAuth2\DataStore interface for
// your own implementation
$dataStore = new myOAuth2\DataStore\Session();

$scope = null;

$service = new myOAuth2\Service($client, $configuration, $dataStore, $scope);

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'authorize':
            // redirects to authorize endpoint
            $service->authorize();
            break;
        case 'requestApi':
            // calls api endpoint with access token
            echo $service->callApiEndpoint('API_ENDPOINT');
            break;
    }
}

if (isset($_GET['code'])) {
    // retrieve access token from endpoint
    $service->getAccessToken();
}

$token = $dataStore->retrieveAccessToken();
```
