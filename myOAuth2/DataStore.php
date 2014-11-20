<?php
namespace myOAuth2;

interface DataStore
{
    /**
     * @param \myOAuth2\Token $token
     */
    function storeAccessToken(Token $token);

    /**
     * @return \myOAuth2\Token
     */
    function retrieveAccessToken();
}
