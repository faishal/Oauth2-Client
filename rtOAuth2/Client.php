<?php
namespace rtOAuth2;

class Client
{
    /**
     * @var string
     */
    private $_clientKey;

    /**
     * @var string
     */
    private $_clientSecret;

    /**
     * @var string
     */
    private $_callbackUrl;

    /**
     *
     * @param string $clientKey
     * @param string $clientSecret
     * @param string $callbackUrl
     */
    public function __construct($clientKey, $clientSecret, $callbackUrl) {
        $this->_clientKey = $clientKey;
        $this->_clientSecret = $clientSecret;
        $this->_callbackUrl = $callbackUrl;
    }

    /**
     * @return string
     */
    public function getClientKey() {
        return $this->_clientKey;
    }

    /**
     * @return string
     */
    public function getClientSecret() {
        return $this->_clientSecret;
    }

    /**
     * @return string
     */
    public function getCallbackUrl() {
        return $this->_callbackUrl;
    }
}
