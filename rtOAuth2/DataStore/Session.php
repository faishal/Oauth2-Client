<?php

namespace rtOAuth2\DataStore;

use rtOAuth2\DataStore;
use rtOAuth2\Token;

class Session implements DataStore
{
    public function __construct() {
        session_start();
    }

    /**
     *
     * @return \rtOAuth2\Token
     */
    public function retrieveAccessToken() {
        return isset($_SESSION['rtOAuth2_token']) ? $_SESSION['rtOAuth2_token'] : new Token();
    }

    /**
     * @param \rtOAuth2\Token $token
     */
    public function storeAccessToken(Token $token) {
        $_SESSION['rtOAuth2_token'] = $token;
    }

    public function  __destruct() {
        session_write_close();
    }
}
