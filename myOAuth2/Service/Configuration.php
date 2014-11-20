<?php
namespace myOAuth2\Service;

class Configuration
{
    const AUTHORIZATION_METHOD_HEADER = 1;
    const AUTHORIZATION_METHOD_ALTERNATIVE = 2;

    /**
     * @var string
     */
    private $_authorizeEndpoint;

    /**
     * @var string
     */
    private $_accessTokenEndpoint;

    /**
     * @var string
     */
    private $_authorizationMethod = self::AUTHORIZATION_METHOD_HEADER;

    /**
     * @param string $authorizeEndpoint
     * @param string $accessTokenEndpoint
     */
    public function __construct($authorizeEndpoint, $accessTokenEndpoint) {
        $this->_authorizeEndpoint = $authorizeEndpoint;
        $this->_accessTokenEndpoint = $accessTokenEndpoint;
    }

    /**
     * @return string
     */
    public function getAuthorizeEndpoint() {
        return $this->_authorizeEndpoint;
    }

    /**
     * @return string
     */
    public function getAccessTokenEndpoint() {
        return $this->_accessTokenEndpoint;
    }

    /**
     * @return string
     */
    public function setAuthorizationMethod($authorizationMethod) {
         $this->_authorizationMethod = $authorizationMethod;
    }

    /**
     * @return string
     */
    public function getAuthorizationMethod() {
        return $this->_authorizationMethod;
    }

}
