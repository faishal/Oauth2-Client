<?php
namespace myOAuth2;

class Token
{
    /**
     * @var string
     */
    private $_accessToken;

    /**
     * @var string
     */
    private $_refreshToken;

    /**
     * @var string
     */
    private $_lifeTime;

    /**
     * @var array
     */
    private $_additionalParams = array();
    /**
     *
     * @param string $accessToken
     * @param string $refreshToken
     * @param int $lifeTime
     */
    public function __construct($accessToken = null, $refreshToken = null, $lifeTime = null) {
        $this->_accessToken = $accessToken;
        $this->_refreshToken = $refreshToken;
        if ($lifeTime) {
            $this->_lifeTime = ((int)$lifeTime) + time();
        }
    }

    /**
     * magic method for setting and getting additional parameters returned from
     * service
     *
     * e.g. user_id parameter with scope openid
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments) {
        if (strlen($name) < 4) {
            throw new Exception('undefined magic method called');
        }
        $method = substr($name, 0, 3);
        $param  = substr($name, 3);
        switch ($method) {
            case 'get':
                if (! isset($this->_additionalParams[$param])) {
                    throw new Exception($param . ' was not returned by service');
                }
                return $this->_additionalParams[$param];
            case 'set':
                if (! array_key_exists(0, $arguments)) {
                    throw new Exception('magic setter has no argument');
                }
                $this->_additionalParams[$param] = $arguments[0];
                break;
            default:
                throw new Exception('undefined magic method called');
        }
    }

    /**
     * @return string
     */
    public function getAccessToken() {
        return $this->_accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken() {
        return $this->_refreshToken;
    }

    /**
     * @return int
     */
    public function getLifeTime() {
        return $this->_lifeTime;
    }
}
