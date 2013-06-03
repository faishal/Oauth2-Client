<?php
namespace rtOAuth2;

class Service
{
    /**
     * @var \rtOAuth2\Client
     */
    private $_client;

    /**
     * @var \rtOAuth2\Service\Configuration
     */
    private $_configuration;

    /**
     * @var \rtOAuth2\DataStore
     */
    private $_dataStore;

    /**
     * @var string
     */
    private $_scope;

    /**
     * @param \rtOAuth2\Client $client
     * @param \rtOAuth2\Service\Configuration $configuration
     * @param \rtOAuth2\DataStore $dataStore
     * @param string $scope optional
     */
    public function  __construct(Client $client,
            Service\Configuration $configuration,
            DataStore $dataStore,
            $scope = null) {
        $this->_client = $client;
        $this->_configuration = $configuration;
        $this->_dataStore = $dataStore;
        $this->_scope = $scope;
    }

    /**
     * redirect to authorize endpoint of service
     */
    public function authorize(array $userParameters = array()) {
       $parameters = array_merge($userParameters, array(
            'type' => 'web_server',
            'client_id' => $this->_client->getClientKey(),
            'redirect_uri' => $this->_client->getCallbackUrl(),
            'response_type' => 'code',
        ));

        if ($this->_scope) {
            $parameters['scope'] = $this->_scope;
        }

        $url = $this->_configuration->getAuthorizeEndpoint();
        $url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($parameters);

        header('Location: ' . $url);
        die();
    }

    /**
     * get access token of from service, has to be called after successful authorization
     *
     * @param string $code optional, if no code given method tries to get it out of $_GET
     */
    public function getAccessToken($code = null) {
        if (! $code) {
            if (! isset($_GET['code'])) {
                throw new Exception('could not retrieve code out of callback request and no code given');
            }
            $code = $_GET['code'];
        }

        $parameters = array(
            'grant_type' => 'authorization_code',
            'type' => 'web_server',
            'client_id' => $this->_client->getClientKey(),
            'client_secret' => $this->_client->getClientSecret(),
            'redirect_uri' => $this->_client->getCallbackUrl(),
            'code' => $code,
        );

        if ($this->_scope) {
            $parameters['scope'] = $this->_scope;
        }

        $http = new HttpClient($this->_configuration->getAccessTokenEndpoint(), 'POST', http_build_query($parameters));
        //$http->setDebug(true);
        $http->execute();

        $this->_parseAccessTokenResponse($http);
    }

    /**
     * refresh access token
     *
     * @param \rtOAuth2\Token $token
     * @return \rtOAuth2\Token new token object
     */
    public function refreshAccessToken(Token $token) {
        if (! $token->getRefreshToken()) {
            throw new Exception('could not refresh access token, no refresh token available');
        }

        $parameters = array(
            'grant_type' => 'refresh_token',
            'type' => 'web_server',
            'client_id' => $this->_client->getClientKey(),
            'client_secret' => $this->_client->getClientSecret(),
            'refresh_token' => $token->getRefreshToken(),
        );

        $http = new HttpClient($this->_configuration->getAccessTokenEndpoint(), 'POST', http_build_query($parameters));
        $http->execute();

        return $this->_parseAccessTokenResponse($http, $token->getRefreshToken());
    }

    /**
     * parse the response of an access token request and store it in dataStore
     *
     * @param \rtOAuth2\HttpClient $http
     * @param string $oldRefreshToken
     * @return \rtOAuth2\Token
     */
    private function _parseAccessTokenResponse(HttpClient $http, $oldRefreshToken = null) {
        $headers = $http->getHeaders();
        $type = 'text';
        if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'application/json') !== false) {
            $type = 'json';
        }

        switch ($type) {
            case 'json':
                $response = json_decode($http->getResponse(), true);
                break;
            case 'text':
            default:
                $response = HttpClient::parseStringToArray($http->getResponse(), '&', '=');
                break;
        }

        if (isset($response['error'])) {
            throw new Exception('got error while requesting access token: ' . $response['error']);
        }
        if (! isset($response['access_token'])) {
            throw new Exception('no access_token found');
        }

        $token = new Token($response['access_token'],
                isset($response['refresh_token']) ? $response['refresh_token'] : $oldRefreshToken,
                isset($response['expires_in']) ? $response['expires_in'] : null);

        unset($response['access_token']);
        unset($response['refresh_token']);
        unset($response['expires_in']);

        // add additional parameters which may be returned depending on service and scope
        foreach ($response as $key => $value) {
            $token->{'set' . $key}($value);
        }

        $this->_dataStore->storeAccessToken($token);

        return $token;
    }

    /**
     * call an api endpoint. automatically adds needed authorization headers with access token or parameters
     *
     * @param string $endpoint
     * @param string $method default 'GET'
     * @param array $uriParameters optional
     * @param mixed $postBody optional, can be string or array
     * @param array $additionalHeaders
     * @return string
     */
    public function callApiEndpoint($endpoint, $method = 'GET', array $uriParameters = array(), $postBody = null, array $additionalHeaders = array()) {
        $token = $this->_dataStore->retrieveAccessToken();

        //check if token is invalid
        if ($token->getLifeTime() && $token->getLifeTime() < time()) {
            $token = $this->refreshAccessToken($token);
        }

        $parameters = null;

        $authorizationMethod = $this->_configuration->getAuthorizationMethod();

        switch ($authorizationMethod) {
            case Service\Configuration::AUTHORIZATION_METHOD_HEADER:
                $additionalHeaders = array_merge(array('Authorization: OAuth ' . $token->getAccessToken()), $additionalHeaders);
                break;
            case Service\Configuration::AUTHORIZATION_METHOD_ALTERNATIVE:
                if ($method !== 'GET') {
                    if (is_array($postBody)) {
                        $postBody['oauth_token'] = $token->getAccessToken();
                    } else {
                        $postBody .= '&oauth_token=' . urlencode($token->getAccessToken());
                    }
                } else {
                    $uriParameters['oauth_token'] = $token->getAccessToken();
                }
                break;
            default:
                throw new Exception("Invalid authorization method specified");
                break;
        }

        if ($method !== 'GET') {
            if (is_array($postBody)) {
                $parameters = http_build_query($postBody);
            } else {
                $parameters = $postBody;
            }
        }

        if (! empty($uriParameters)) {
            $endpoint .= (strpos($endpoint, '?') !== false ? '&' : '?') . http_build_query($uriParameters);
        }


        $http = new HttpClient($endpoint, $method, $parameters, $additionalHeaders);
        $http->execute();

        return $http->getResponse();
    }
}
