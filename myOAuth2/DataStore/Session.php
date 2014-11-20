<?php

namespace myOAuth2\DataStore;

use myOAuth2\DataStore;
use myOAuth2\Token;

class Session implements DataStore
{
    public function __construct() {
        session_start();
    }

    /**
     *
     * @return \myOAuth2\Token
     */
    public function retrieveAccessToken() {
        return isset($_SESSION['myOAuth2_token']) ? $_SESSION['myOAuth2_token'] : new Token();
    }

    /**
     * @param \myOAuth2\Token $token
     */
    public function storeAccessToken(Token $token) {
        $_SESSION['myOAuth2_token'] = $token;
    }

    public function  __destruct() {
        session_write_close();
    }
}
